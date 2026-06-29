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

    $checkStmt = $dbPdo->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->execute(['admin']);

    if (!$checkStmt->fetch()) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insertStmt = $dbPdo->prepare("INSERT INTO users (full_name, email, username, password_hash) VALUES (?, ?, ?, ?)");
        $insertStmt->execute(['Admin User', 'admin@example.com', 'admin', $hash]);
    }

    return $dbPdo;
}

$pdo = initializeDatabase($host, $dbName, $username, $password);
