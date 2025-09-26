<?php
// Always start with error reporting ON
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start clean – remove any accidental whitespace
ob_start();
header('Content-Type: application/json');

// include config
include __DIR__ . '/../config.php';


// after including, clear output buffer (if config.php produced warnings)
if (ob_get_length()) ob_clean();

// session start AFTER headers
session_start();

$user_id = $_SESSION['user_id'] ?? null;
$storeCode = $_SESSION['store_code'] ?? '';
$type = $storeCode === 'KM' ? 'coffee' : 'online';

$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_SPECIAL_CHARS);

if (!isset($conn) || !$conn) {
    echo json_encode(['status' => 'error', 'message' => 'No DB connection']);
    exit;
}

// If not logged in → JSON error
if (!$user_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in',
        'redirect' => 'login.php'
    ]);
    exit;
}

$message = null;


if ($action === 'add_to_wishlist' && $product_id) {
    try {
        $check_favourite = $conn->prepare(
            "SELECT 1 FROM `wishlist` WHERE product_id = ? AND user_id = ? AND type = ?"
        );
        $check_favourite->execute([$product_id, $user_id, $type]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }


    if ($check_favourite->rowCount() > 0) {
        $message = 'Already added to wishlist!';
    } else {
        $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $select_product->execute([$product_id]);

        if ($select_product->rowCount() > 0) {
            $conn->prepare(
                "INSERT INTO `wishlist` (user_id, product_id, type) VALUES(?, ?, ?)"
            )->execute([$user_id, $product_id, $type]);
            $message = 'Added to wishlist!';
        } else {
            $message = 'Product not found!';
        }
    }
}

echo json_encode([
    'status' => 'success',
    'message' => $message ?? 'No action performed'
]);
exit;
