<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->query('SELECT c.*, COUNT(i.id) AS installments_count, IFNULL(SUM(i.remaining_amount),0) AS total_remaining FROM customers c LEFT JOIN installments i ON i.customer_id = c.id GROUP BY c.id ORDER BY c.id DESC');
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Report - Installment System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
            <div class="app-header">
                <div class="logo">Customer Report</div>
                <div class="nav-links">
                    <a href="../dashboard.php">Dashboard</a>
                    <a href="../customers.php">Customers</a>
                    <a href="../products.php">Products</a>
                    <a href="../installments.php">Installments</a>
                    <a href="../payments.php">Payments</a>
                </div>
            </div>
            <h1>Customer Report</h1>
        <p class="subtitle">List of customers and their installment summary.</p>

        <div class="actions">
            <a href="../dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <table class="table" style="margin-top:18px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>CNIC</th>
                    <th>Installments</th>
                    <th>Total Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?php echo (int)$c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['phone']); ?></td>
                        <td><?php echo htmlspecialchars($c['cnic']); ?></td>
                        <td><?php echo (int)$c['installments_count']; ?></td>
                        <td><?php echo number_format((float)$c['total_remaining'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
