<?php
require_once __DIR__ . '/config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
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
    <div class="card">
        <h1>Dashboard</h1>
        <p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</p>

        <div class="info-box">
            <p>You have successfully logged in to the simple installment management system.</p>
            <p>Current user: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></p>
        </div>

        <div class="actions">
            <a href="customers.php" class="button-link">Manage Customers</a>
            <a href="products.php" class="button-link">Manage Products</a>
            <a href="logout.php" class="button-link">Logout</a>
        </div>
    </div>
</body>
</html>
