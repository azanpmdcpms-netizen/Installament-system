<?php
require_once __DIR__ . '/config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT id, full_name, username, password_hash FROM users WHERE email = ? OR username = ?');
        $stmt->execute([$email, $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username'] = $user['username'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installment Management System — Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="page-shell">
        <div class="card">
            <h1>Installment Management System</h1>
            <p class="subtitle">Sign in to your account</p>

            <?php if ($error !== ''): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>

            <p class="hint">Demo credentials: admin / admin123 (username or email accepted)</p>
        </div>
    </div>
</body>
</html>
