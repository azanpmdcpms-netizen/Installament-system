<?php
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->query('SELECT i.*, c.name AS customer_name, pr.name AS product_name FROM installments i LEFT JOIN customers c ON c.id = i.customer_id LEFT JOIN products pr ON pr.id = i.product_id WHERE i.remaining_amount > 0 ORDER BY i.id DESC');
$dues = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Due Installments - Installment System</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card card-wide">
            <h1>Due Installments</h1>
        <p class="subtitle">Installments with outstanding balances.</p>

        <div class="actions">
            <a href="../dashboard.php" class="button-link secondary">Back to Dashboard</a>
        </div>

        <table class="table" style="margin-top:18px;">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Down</th>
                    <th>Remaining</th>
                    <th>Months</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dues as $d): ?>
                    <tr>
                        <td><?php echo (int)$d['id']; ?></td>
                        <td><?php echo htmlspecialchars($d['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($d['product_name']); ?></td>
                        <td><?php echo number_format((float)$d['total_price'], 2); ?></td>
                        <td><?php echo number_format((float)$d['down_payment'], 2); ?></td>
                        <td><?php echo number_format((float)$d['remaining_amount'], 2); ?></td>
                        <td><?php echo (int)$d['months']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
