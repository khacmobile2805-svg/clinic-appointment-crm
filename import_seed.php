<?php
$dbConfig = require 'config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = file_get_contents('database/seed.sql');
    $pdo->exec($sql);
    echo "Seed data imported successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
