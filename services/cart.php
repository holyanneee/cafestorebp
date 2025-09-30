<?php
declare(strict_types=1);

// Debug + JSON header
error_reporting(E_ALL);
ini_set('display_errors', '1');
ob_start();
header('Content-Type: application/json');

// Config & session
require __DIR__ . '/../config.php';
session_start();

// Clean output buffer after config if needed
if (ob_get_length())
    ob_clean();

$user_id = $_SESSION['user_id'] ?? null;
$action = $_POST['action'] ?? '';
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;

if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'No DB connection']);
    exit;
}
if (!$user_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in',
        'redirect' => 'login.php'
    ]);
    exit;
}

if ($product_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}
if ($action == 'add_to_cart') {
    try {
        // Check if product exists once
        $productStmt = $conn->prepare("SELECT * FROM `products` WHERE id=? LIMIT 1");
        $productStmt->execute([$product_id]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit;
        }

        // Check if already in cart
        $cartStmt = $conn->prepare("SELECT * FROM `cart` WHERE product_id=? AND user_id=? LIMIT 1");
        $cartStmt->execute([$product_id, $user_id]);
        $cartItem = $cartStmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Increment quantity & recalc subtotal
            $newQuantity = $cartItem['quantity'] + 1;
            $newSubtotal = $product['price'] * $newQuantity; // or use cup price if coffee
            $upd = $conn->prepare("UPDATE `cart` SET quantity=?, subtotal=? WHERE product_id=? AND user_id=?");
            $upd->execute([$newQuantity, $newSubtotal, $product_id, $user_id]);

            $message = 'Quantity updated in cart!';
        } else {
            // If new to cart → remove from wishlist
            $conn->prepare("DELETE FROM `wishlist` WHERE product_id=? AND user_id=?")
                ->execute([$product_id, $user_id]);

            if ($product['type'] === 'coffee') {
                // Ingredients (single loop)
                $ingredients = [];
                $ingredientIds = json_decode($product['ingredients'] ?? '[]', true);
                if (!empty($ingredientIds)) {
                    $placeholders = implode(',', array_fill(0, count($ingredientIds), '?'));
                    $ingStmt = $conn->prepare("SELECT id,name FROM `ingredients` WHERE id IN ($placeholders)");
                    $ingStmt->execute($ingredientIds);
                    foreach ($ingStmt->fetchAll(PDO::FETCH_ASSOC) as $ing) {
                        $ingredients[$ing['id']] = ['name' => $ing['name'], 'level' => 'Regular'];
                    }
                }

                // Cup sizes
                $cupSizes = json_decode($product['cup_sizes'] ?? '{}', true);
                $cup_size = isset($cupSizes['regular']) ? 'Regular' : 'Small';
                $cup_price = $cupSizes[strtolower($cup_size)] ?? 0;

                $subtotal = $product['price'] + $cup_price;

                $insert = $conn->prepare("INSERT INTO `cart` 
                    (user_id, product_id, quantity, ingredients, cup_size, subtotal, type) 
                    VALUES (?, ?, 1, ?, ?, ?, 'coffee')");
                $insert->execute([
                    $user_id,
                    $product_id,
                    json_encode($ingredients),
                    json_encode(['size' => $cup_size, 'price' => $cup_price]),
                    $subtotal
                ]);
            } else {
                $insert = $conn->prepare("INSERT INTO `cart`
                    (user_id, product_id, subtotal, quantity, type) 
                    VALUES (?, ?, ?, 1, 'religious')");
                $insert->execute([$user_id, $product_id, $product['price']]);
            }

            $message = 'Added to cart!';
        }

        echo json_encode(['status' => 'success', 'message' => $message]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}




