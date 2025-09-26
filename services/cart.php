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
if (ob_get_length())
    ob_clean();

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



if ($action === 'add_to_cart' && $product_id) {
    $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE product_id = ? AND user_id = ? AND type = ?");
    $check_cart->execute([$product_id, $user_id, $type]);

    if ($check_cart->rowCount() > 0) {
        $conn->prepare("UPDATE `cart` SET quantity = quantity + 1 WHERE product_id = ? AND user_id = ? AND type = ?")
            ->execute([$product_id, $user_id, $type]);

        $cart_item = $check_cart->fetch(PDO::FETCH_ASSOC);
        $subtotal = $cart_item['subtotal'] * $cart_item['quantity'];
        $conn->prepare("UPDATE `cart` SET subtotal = ? WHERE product_id = ? AND user_id = ? AND type = ?")
            ->execute([$subtotal, $product_id, $user_id, $type]);

        $message = 'Quantity updated in cart!';
    } else {
        $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ? AND user_id = ? AND type = ?")
            ->execute([$product_id, $user_id, $type]);

        $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
        $select_product->execute([$product_id]);
        $product = $select_product->fetch(PDO::FETCH_ASSOC);

        if ($type === 'coffee') {
            $ingredients = [];
            foreach (json_decode($product['ingredients'] ?? '[]') as $ingredient_id) {
                $ingredient_stmt = $conn->prepare("SELECT * FROM `ingredients` WHERE id = ?");
                $ingredient_stmt->execute([$ingredient_id]);
                $ingredient = $ingredient_stmt->fetch(PDO::FETCH_ASSOC);
                if ($ingredient) {
                    $ingredients[$ingredient['id']] = ['name' => $ingredient['name'], 'level' => 'Regular'];
                }
            }

            $cup_sizes = json_decode($product['cup_sizes'] ?? '{}', true);
            $cup_size = isset($cup_sizes['regular']) ? 'Regular' : 'Small';
            $cup_price = $cup_sizes[strtolower($cup_size)] ?? 0;

            $conn->prepare("INSERT INTO `cart`(user_id, product_id, quantity, ingredients, cup_size, subtotal, type) 
                      VALUES(?, ?, 1, ?, ?, ?, 'coffee')")
                ->execute([
                    $user_id,
                    $product_id,
                    json_encode($ingredients),
                    json_encode(['size' => $cup_size, 'price' => $cup_price]),
                    ($product['price'] + $cup_price)
                ]);
        } else {
            $conn->prepare("INSERT INTO `cart`(user_id, product_id, subtotal, quantity, type) 
                      VALUES(?, ?, ?, 1, 'online')")
                ->execute([$user_id, $product_id, $product['price']]);
        }
        $message = 'Added to cart!';
    }
}

echo json_encode([
    'status' => 'success',
    'message' => $message ?? 'No action performed'
]);
?>