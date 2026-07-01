<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->query('SELECT p.*, i.id AS installment_id, c.name AS customer_name, pr.name AS product_name FROM payments p LEFT JOIN installments i ON i.id = p.installment_id LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products pr ON pr.id = i.product_id ORDER BY p.payment_date DESC, p.id DESC');
$payments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Report - Installment System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
            <h1>Payment Report</h1>
        <p class="subtitle">All recorded payments.</p>

        <div class="actions">
            <a href="../dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <table class="table" style="margin-top:18px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Installment</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                    <tr>
                        <td><?php echo (int)$p['id']; ?></td>
                        <td><?php echo (int)$p['installment_id']; ?></td>
                        <td><?php echo htmlspecialchars($p['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                        <td><?php echo number_format((float)$p['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($p['payment_date']); ?></td>
                        <td><?php echo htmlspecialchars($p['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
