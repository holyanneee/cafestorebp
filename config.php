<?php
$host = "localhost";
$dbname = "shop_db";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Debug log only (no HTML!)
    echo json_encode(['status' => 'error', 'message' => 'DB Connection failed: '.$e->getMessage()]);
    exit;
}
