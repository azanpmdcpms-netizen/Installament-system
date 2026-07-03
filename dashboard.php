<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$totalCustomers = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$totalProducts = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$activeInstallments = (int)$pdo->query('SELECT COUNT(*) FROM installments WHERE remaining_amount > 0')->fetchColumn();
$totalPayments = (int)$pdo->query('SELECT COUNT(*) FROM payments')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Installment System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card">
            <div class="app-header">
                <div class="logo">Dashboard</div>
                <div class="nav-links">
                    <a href="customers.php">Customers</a>
                    <a href="products.php">Products</a>
                    <a href="installments.php">Installments</a>
                    <a href="payments.php">Payments</a>
                    <a href="reports/customer_report.php">Reports</a>
                </div>
            </div>
            <h1>Dashboard</h1>
        <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>

        <div class="info-box">
            <p>You have successfully logged in to the installment management system.</p>
            <p>Current user: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        </div>

        <div class="dashboard-grid" style="margin-top:18px;">
            <a href="customers.php" class="stat-card">
                <div class="stat-value"><?php echo $totalCustomers; ?></div>
                <div class="stat-label">Total Customers</div>
            </a>

            <a href="products.php" class="stat-card">
                <div class="stat-value"><?php echo $totalProducts; ?></div>
                <div class="stat-label">Total Products</div>
            </a>

            <a href="installments.php" class="stat-card">
                <div class="stat-value"><?php echo $activeInstallments; ?></div>
                <div class="stat-label">Active Installments</div>
            </a>

            <a href="payments.php" class="stat-card">
                <div class="stat-value"><?php echo $totalPayments; ?></div>
                <div class="stat-label">Total Payments</div>
            </a>
        </div>

        <div class="actions" style="margin-top:18px;">
            <a href="customers.php" class="button-link">Manage Customers</a>
            <a href="products.php" class="button-link">Manage Products</a>
            <a href="installments.php" class="button-link">Manage Installments</a>
            <a href="payments.php" class="button-link">Manage Payments</a>
            <a href="reports/customer_report.php" class="button-link">Customer Report</a>
            <a href="reports/payment_report.php" class="button-link">Payment Report</a>
            <a href="reports/due_installments.php" class="button-link">Due Installments</a>
            <a href="logout.php" class="button-link">Logout</a>
        </div>
    </div>
</body>
</html>
