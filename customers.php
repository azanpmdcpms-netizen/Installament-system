<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$message = '';
$mode = 'list';
$search = trim($_GET['search'] ?? '');
$customer = ['id' => '', 'name' => '', 'phone' => '', 'cnic' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id = ?');
        $stmt->execute([$_POST['delete_id']]);
        $message = 'Customer deleted successfully.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $cnic = trim($_POST['cnic'] ?? '');
        $address = trim($_POST['address'] ?? '');

        if ($name === '' || $phone === '' || $cnic === '' || $address === '') {
            $message = 'Please fill in all fields.';
        } else {
            $id = $_POST['customer_id'] ?? '';
            if ($id !== '') {
                $stmt = $pdo->prepare('UPDATE customers SET name = ?, phone = ?, cnic = ?, address = ? WHERE id = ?');
                $stmt->execute([$name, $phone, $cnic, $address, $id]);
                $message = 'Customer updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO customers (name, phone, cnic, address) VALUES (?, ?, ?, ?)');
                $stmt->execute([$name, $phone, $cnic, $address]);
                $message = 'Customer added successfully.';
            }
        }
    }
}

if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$_GET['edit_id']]);
    $customer = $stmt->fetch();
    $mode = 'edit';
}

if (isset($_GET['view_id'])) {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE id = ?');
    $stmt->execute([$_GET['view_id']]);
    $customer = $stmt->fetch();
    $mode = 'view';
}

$whereClause = '';
$params = [];
if ($search !== '') {
    $whereClause = 'WHERE name LIKE ? OR phone LIKE ? OR cnic LIKE ? OR address LIKE ?';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term, $term];
}

$stmt = $pdo->prepare('SELECT * FROM customers ' . $whereClause . ' ORDER BY id DESC');
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Installment System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
        <div class="app-header">
            <div class="logo">Customer Module</div>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="products.php">Products</a>
                <a href="installments.php">Installments</a>
                <a href="payments.php">Payments</a>
            </div>
        </div>
        <h1>Customer Module</h1>
        <p class="subtitle">Add, view, edit, and delete customers.</p>

        <?php if ($message !== ''): ?>
            <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="actions">
            <a href="dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <div class="form-grid" style="margin-top: 20px;">
            <form method="post">
                <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customer['id'] ?? ''); ?>">
                <label for="name">Customer Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required>

                <label for="phone">Phone</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required>

                <label for="cnic">CNIC</label>
                <input type="text" id="cnic" name="cnic" value="<?php echo htmlspecialchars($customer['cnic'] ?? ''); ?>" required>

                <label for="address">Address</label>
                <textarea id="address" name="address" required><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>

                <button type="submit"><?php echo $customer['id'] ? 'Update Customer' : 'Add Customer'; ?></button>
                <?php if ($customer['id']): ?>
                    <a href="customers.php" class="button-link secondary">Cancel</a>
                <?php endif; ?>
            </form>
        </div>

        <form method="get" class="search-bar">
            <input type="search" name="search" class="search-input" placeholder="Search customers..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="button-link search-button">Search</button>
            <?php if ($search !== ''): ?>
                <a href="customers.php" class="button-link secondary">Clear</a>
            <?php endif; ?>
        </form>

        <h2 style="margin-top: 30px;">Customer List</h2>
        <?php if (empty($customers)): ?>
            <p class="empty-state">No customers found yet.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>CNIC</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customerRow): ?>
                        <tr>
                            <td><?php echo (int)$customerRow['id']; ?></td>
                            <td><?php echo htmlspecialchars($customerRow['name']); ?></td>
                            <td><?php echo htmlspecialchars($customerRow['phone']); ?></td>
                            <td><?php echo htmlspecialchars($customerRow['cnic']); ?></td>
                            <td><?php echo htmlspecialchars($customerRow['address']); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="customers.php?view_id=<?php echo (int)$customerRow['id']; ?>" class="button-link secondary">View</a>
                                    <a href="customers.php?edit_id=<?php echo (int)$customerRow['id']; ?>" class="button-link">Edit</a>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Delete this customer?');">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$customerRow['id']; ?>">
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
