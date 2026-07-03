<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$search = trim($_GET['search'] ?? '');
$viewInstallment = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM installments WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $message = 'Installment deleted successfully.';
    } else {
        $customerId = (int)($_POST['customer_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);
        $totalPrice = trim($_POST['total_price'] ?? '');
        $downPayment = trim($_POST['down_payment'] ?? '');
        $monthlyInstallment = trim($_POST['monthly_installment'] ?? '');
        $months = trim($_POST['months'] ?? '');

        if ($customerId <= 0 || $productId <= 0 || $totalPrice === '' || $downPayment === '' || $monthlyInstallment === '' || $months === '') {
            $message = 'Please fill in all fields.';
        } elseif (!is_numeric($totalPrice) || (float)$totalPrice < 0 || !is_numeric($downPayment) || (float)$downPayment < 0 || !is_numeric($monthlyInstallment) || (float)$monthlyInstallment < 0 || !is_numeric($months) || (int)$months <= 0) {
            $message = 'Please enter valid installment values.';
        } elseif ((float)$downPayment > (float)$totalPrice) {
            $message = 'Down payment cannot exceed the total price.';
        } else {
            $remainingAmount = max(0, (float)$totalPrice - (float)$downPayment);
            $stmt = $pdo->prepare('INSERT INTO installments (customer_id, product_id, total_price, down_payment, monthly_installment, months, remaining_amount) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$customerId, $productId, $totalPrice, $downPayment, $monthlyInstallment, (int)$months, $remainingAmount]);
            $message = 'Installment created successfully.';
        }
    }
}

if (isset($_GET['view_id'])) {
    $stmt = $pdo->prepare('SELECT i.*, c.name AS customer_name, p.name AS product_name FROM installments i LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products p ON p.id = i.product_id WHERE i.id = ?');
    $stmt->execute([$_GET['view_id']]);
    $viewInstallment = $stmt->fetch();
}

$whereClause = '';
$params = [];
if ($search !== '') {
    $whereClause = 'WHERE c.name LIKE ? OR p.name LIKE ? OR i.id LIKE ?';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term];
}

$stmt = $pdo->prepare('SELECT i.*, c.name AS customer_name, p.name AS product_name FROM installments i LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products p ON p.id = i.product_id ' . $whereClause . ' ORDER BY i.id DESC');
$stmt->execute($params);
$installments = $stmt->fetchAll();

$customers = $pdo->query('SELECT id, name FROM customers ORDER BY name')->fetchAll();
$products = $pdo->query('SELECT id, name FROM products ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installments - Installment System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
        <div class="app-header">
            <div class="logo">Installment Module</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <a href="products.php">Products</a>
                <a href="payments.php">Payments</a>
            </div>
        </div>
        <h1>Installment Module</h1>
        <p class="subtitle">Create and review installment plans.</p>

        <?php if ($message !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="actions">
            <a href="dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <div class="form-grid" style="margin-top: 20px;">
            <form method="post">
                <label for="customer_id">Customer</label>
                <select id="customer_id" name="customer_id" required>
                    <option value="">Select customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo (int)$customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="product_id">Product</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo (int)$product['id']; ?>"><?php echo htmlspecialchars($product['name']); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="total_price">Total Price</label>
                <input type="number" step="0.01" id="total_price" name="total_price" required>

                <label for="down_payment">Down Payment</label>
                <input type="number" step="0.01" id="down_payment" name="down_payment" required>

                <label for="monthly_installment">Monthly Installment</label>
                <input type="number" step="0.01" id="monthly_installment" name="monthly_installment" required>

                <label for="months">Months</label>
                <input type="number" id="months" name="months" min="1" required>

                <label for="remaining_amount">Remaining Amount</label>
                <input type="number" step="0.01" id="remaining_amount" name="remaining_amount" readonly value="0">

                <button type="submit">Create Installment</button>
            </form>
        </div>

        <?php if ($viewInstallment): ?>
            <h2 style="margin-top: 30px;">Installment Details</h2>
            <div class="info-box">
                <p><strong>Installment ID:</strong> <?php echo (int)$viewInstallment['id']; ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($viewInstallment['customer_name']); ?></p>
                <p><strong>Product:</strong> <?php echo htmlspecialchars($viewInstallment['product_name']); ?></p>
                <p><strong>Total Price:</strong> <?php echo number_format((float)$viewInstallment['total_price'], 2); ?></p>
                <p><strong>Down Payment:</strong> <?php echo number_format((float)$viewInstallment['down_payment'], 2); ?></p>
                <p><strong>Monthly Installment:</strong> <?php echo number_format((float)$viewInstallment['monthly_installment'], 2); ?></p>
                <p><strong>Months:</strong> <?php echo (int)$viewInstallment['months']; ?></p>
                <p><strong>Remaining Amount:</strong> <?php echo number_format((float)$viewInstallment['remaining_amount'], 2); ?></p>
            </div>
        <?php endif; ?>

        <form method="get" class="search-bar">
            <input type="search" name="search" class="search-input" placeholder="Search installments..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="button-link search-button">Search</button>
            <?php if ($search !== ''): ?>
                <a href="installments.php" class="button-link secondary">Clear</a>
            <?php endif; ?>
        </form>

        <h2 style="margin-top: 30px;">Installment List</h2>
        <?php if (empty($installments)): ?>
            <p class="empty-state">No installments found yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Total</th>
                        <th>Down</th>
                        <th>Monthly</th>
                        <th>Months</th>
                        <th>Remaining</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($installments as $installmentRow): ?>
                        <tr>
                            <td><?php echo (int)$installmentRow['id']; ?></td>
                            <td><?php echo htmlspecialchars($installmentRow['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($installmentRow['product_name']); ?></td>
                            <td><?php echo number_format((float)$installmentRow['total_price'], 2); ?></td>
                            <td><?php echo number_format((float)$installmentRow['down_payment'], 2); ?></td>
                            <td><?php echo number_format((float)$installmentRow['monthly_installment'], 2); ?></td>
                            <td><?php echo (int)$installmentRow['months']; ?></td>
                            <td><?php echo number_format((float)$installmentRow['remaining_amount'], 2); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="installments.php?view_id=<?php echo (int)$installmentRow['id']; ?>" class="button-link secondary">View</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this installment?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$installmentRow['id']; ?>">
                                        <button type="submit" class="danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        const totalPriceInput = document.getElementById('total_price');
        const downPaymentInput = document.getElementById('down_payment');
        const remainingAmountInput = document.getElementById('remaining_amount');

        function updateRemainingAmount() {
            const total = parseFloat(totalPriceInput.value) || 0;
            const down = parseFloat(downPaymentInput.value) || 0;
            const remaining = Math.max(0, total - down);
            remainingAmountInput.value = remaining.toFixed(2);
        }

        [totalPriceInput, downPaymentInput].forEach((input) => {
            input.addEventListener('input', updateRemainingAmount);
        });
    </script>
</body>
</html>
