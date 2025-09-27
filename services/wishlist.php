<?php
declare(strict_types=1);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start buffering early
ob_start();

// Include config & start session
require __DIR__ . '/../config.php';
session_start();

// Send JSON header AFTER including config
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Ensure no stray output
if (ob_get_length()) ob_clean();

// Check DB connection
if (empty($conn)) {
    echo json_encode(['status' => 'error', 'message' => 'No DB connection']);
    exit;
}

// Logged in?
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in',
        'redirect' => 'login.php'
    ]);
    exit;
}

// Inputs
$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if ($action !== 'add_to_wishlist' || $product_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

try {
    // Check if already in wishlist
    $check = $conn->prepare("SELECT 1 FROM `wishlist` WHERE product_id=? AND user_id=? LIMIT 1");
    $check->execute([$product_id, $user_id]);
    if ($check->fetchColumn()) {
        echo json_encode(['status' => 'info', 'message' => 'Already added to wishlist!']);
        exit;
    }

    // Verify product exists
    $productStmt = $conn->prepare("SELECT id FROM `products` WHERE id=? LIMIT 1");
    $productStmt->execute([$product_id]);
    if (!$productStmt->fetchColumn()) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found!']);
        exit;
    }

    // Insert WITHOUT type column
    $insert = $conn->prepare(
        "INSERT INTO `wishlist` (user_id, product_id) VALUES (?, ?)"
    );
    $insert->execute([$user_id, $product_id]);

    echo json_encode(['status' => 'success', 'message' => 'Added to wishlist!']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
exit;
