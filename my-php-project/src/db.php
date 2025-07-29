<?php
function getPDO() {
    $host = 'localhost';
    $db = 'copilot';   // Your database name
    $user = 'root';    // Your database username
    $pass = '';        // Your database password
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        die("DB connection failed: " . $e->getMessage());
    }
}
?>
