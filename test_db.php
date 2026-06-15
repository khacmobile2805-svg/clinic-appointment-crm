<?php
$dbConfig = require 'config/database.php';
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt1 = $pdo->query('SELECT count(*) FROM users');
    echo "Users: " . $stmt1->fetchColumn() . "\n";
    
    $stmt2 = $pdo->query('SELECT count(*) FROM patients');
    echo "Patients: " . $stmt2->fetchColumn() . "\n";

    $stmt3 = $pdo->query('SELECT count(*) FROM appointments');
    echo "Appointments: " . $stmt3->fetchColumn() . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
