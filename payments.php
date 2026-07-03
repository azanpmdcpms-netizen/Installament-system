<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$search = trim($_GET['search'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM payments WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $message = 'Payment deleted successfully.';
    } else {
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $amount = trim($_POST['amount'] ?? '');
        $paymentDate = trim($_POST['payment_date'] ?? '');
        $status = trim($_POST['status'] ?? 'Paid');

        if ($installmentId <= 0 || $amount === '' || $paymentDate === '') {
            $message = 'Please fill in all fields.';
        } elseif (!is_numeric($amount) || (float)$amount <= 0) {
            $message = 'Please enter a valid amount.';
        } else {
            $remainingStmt = $pdo->prepare('SELECT remaining_amount FROM installments WHERE id = ?');
            $remainingStmt->execute([$installmentId]);
            $remainingRow = $remainingStmt->fetch();

            if (!$remainingRow) {
                $message = 'Selected installment could not be found.';
            } elseif ((float)$amount > (float)$remainingRow['remaining_amount']) {
                $message = 'Amount cannot exceed the remaining installment balance.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO payments (installment_id, amount, payment_date, status) VALUES (?, ?, ?, ?)');
                $stmt->execute([$installmentId, $amount, $paymentDate, $status]);

                $newRemaining = max(0, (float)$remainingRow['remaining_amount'] - (float)$amount);
                $updateStmt = $pdo->prepare('UPDATE installments SET remaining_amount = ? WHERE id = ?');
                $updateStmt->execute([$newRemaining, $installmentId]);

                $message = 'Payment recorded successfully.';
            }
        }
    }
}

$whereClause = '';
$params = [];
if ($search !== '') {
    $whereClause = 'WHERE c.name LIKE ? OR pr.name LIKE ? OR p.payment_date LIKE ? OR p.status LIKE ?';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term, $term];
}

$stmt = $pdo->prepare('SELECT p.*, i.id AS installment_id, c.name AS customer_name, pr.name AS product_name FROM payments p LEFT JOIN installments i ON i.id = p.installment_id LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products pr ON pr.id = i.product_id ' . $whereClause . ' ORDER BY p.payment_date DESC, p.id DESC');
$stmt->execute($params);
$payments = $stmt->fetchAll();

$installments = $pdo->query('SELECT i.id, c.name AS customer_name, pr.name AS product_name FROM installments i LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products pr ON pr.id = i.product_id ORDER BY i.id DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Installment System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
        <div class="app-header">
            <div class="logo">Payment Module</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="customers.php">Customers</a>
                <a href="products.php">Products</a>
                <a href="installments.php">Installments</a>
            </div>
        </div>
        <h1>Payment Module</h1>
        <p class="subtitle">Add payments and review payment history.</p>

        <?php if ($message !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="actions">
            <a href="dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <div class="form-grid" style="margin-top: 20px;">
            <form method="post">
                <label for="installment_id">Installment</label>
                <select id="installment_id" name="installment_id" required>
                    <option value="">Select installment</option>
                    <?php foreach ($installments as $installment): ?>
                        <option value="<?php echo (int)$installment['id']; ?>">
                            <?php echo htmlspecialchars($installment['customer_name'] . ' - ' . $installment['product_name'] . ' (#' . $installment['id'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="amount">Amount</label>
                <input type="number" step="0.01" id="amount" name="amount" required>

                <label for="payment_date">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date" required>

                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                </select>

                <button type="submit">Add Payment</button>
            </form>
        </div>

        <form method="get" class="search-bar">
            <input type="search" name="search" class="search-input" placeholder="Search payments..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="button-link search-button">Search</button>
            <?php if ($search !== ''): ?>
                <a href="payments.php" class="button-link secondary">Clear</a>
            <?php endif; ?>
        </form>

        <h2 style="margin-top: 30px;">Payment History</h2>
        <?php if (empty($payments)): ?>
            <p class="empty-state">No payments found yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Installment</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $paymentRow): ?>
                        <tr>
                            <td><?php echo (int)$paymentRow['id']; ?></td>
                            <td><?php echo (int)$paymentRow['installment_id']; ?></td>
                            <td><?php echo htmlspecialchars($paymentRow['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($paymentRow['product_name']); ?></td>
                            <td><?php echo number_format((float)$paymentRow['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($paymentRow['payment_date']); ?></td>
                            <td><?php echo htmlspecialchars($paymentRow['status']); ?></td>
                            <td>
                                <div class="table-actions">
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this payment?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$paymentRow['id']; ?>">
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
</body>
</html>
