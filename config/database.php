<?php
session_start();

$host = '127.0.0.1';
$dbName = 'installment_db';
$username = 'root';
$password = '';

function initializeDatabase(string $host, string $dbName, string $username, string $password): PDO
{
    $serverPdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    $dbPdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $dbPdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $requiredColumns = [
        'full_name' => "VARCHAR(100) NOT NULL DEFAULT '' AFTER id",
        'email' => "VARCHAR(100) NOT NULL DEFAULT '' AFTER full_name",
        'username' => "VARCHAR(50) NOT NULL DEFAULT '' AFTER email",
        'password_hash' => "VARCHAR(255) NOT NULL DEFAULT '' AFTER username",
    ];

    foreach ($requiredColumns as $column => $definition) {
        $columnCheck = $dbPdo->query("SHOW COLUMNS FROM users LIKE '$column'")->fetch();
        if (!$columnCheck) {
            $dbPdo->exec("ALTER TABLE users ADD COLUMN $column $definition");
        }
    }

    $dbPdo->exec(
        "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            cnic VARCHAR(15) NOT NULL UNIQUE,
            address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $checkStmt = $dbPdo->prepare("SELECT id, full_name, email, password_hash FROM users WHERE username = ?");
    $checkStmt->execute(['admin']);
    $adminUser = $checkStmt->fetch();
    $hash = password_hash('admin123', PASSWORD_DEFAULT);

    if (!$adminUser) {
        $insertStmt = $dbPdo->prepare("INSERT INTO users (full_name, email, username, password_hash) VALUES (?, ?, ?, ?)");
        $insertStmt->execute(['Admin User', 'admin@example.com', 'admin', $hash]);
    } else {
        $updateFields = [];
        $params = [];

        if (empty($adminUser['full_name'])) {
            $updateFields[] = 'full_name = ?';
            $params[] = 'Admin User';
        }

        if (empty($adminUser['email'])) {
            $updateFields[] = 'email = ?';
            $params[] = 'admin@example.com';
        }

        if (empty($adminUser['password_hash'])) {
            $updateFields[] = 'password_hash = ?';
            $params[] = $hash;
        }

        if (!empty($updateFields)) {
            $params[] = 'admin';
            $dbPdo->prepare('UPDATE users SET ' . implode(', ', $updateFields) . ' WHERE username = ?')->execute($params);
        }
    }

    return $dbPdo;
}

$pdo = initializeDatabase($host, $dbName, $username, $password);
