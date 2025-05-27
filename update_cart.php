<?php
@include 'config.php';
session_start();

header('Content-Type: application/json');

// Development only
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $current_store = $_SESSION['store'] ?? null;
    $type = ($current_store === 'kape_milagrosa') ? 'coffee' : 'online';
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // DELETE
        if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['cart_id'])) {
            $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);

            echo json_encode(['success' => true]);
            exit;
        }

        // UPDATE
        if (isset($_POST['id'])) {
            $cart_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
            $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_NUMBER_INT);
            $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
            $cup_size = $_POST['cup_size'];
            $ingredients = $_POST['ingredients'];
            $add_ons = $_POST['add_ons'];
            $special_instructions = filter_var($_POST['special_instructions'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            // Validate JSON
            json_decode($cup_size, true);
            json_decode($ingredients, true);
            json_decode($add_ons, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
                exit;
            }

            $stmt = $conn->prepare("UPDATE cart 
                SET quantity = ?, cup_size = ?, ingredients = ?, add_ons = ?, special_instruction = ?
                WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $quantity,
                $cup_size,
                $ingredients,
                $add_ons,
                $special_instructions,
                $cart_id,
                $user_id
            ]);

            echo json_encode(['success' => true]);
            exit;
        }

        // CHECKOUT
        if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
            $name = $_POST['name'];
            $number = $_POST['number'];
            $email = $_POST['email'];
            $method = $_POST['method'];
            $address = $_POST['address'];

            // Get user's cart
            $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($cartItems) === 0) {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                exit;
            }

            // Insert into orders
            $stmt = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $name, $number, $email, $method, $address, 'on Queue']);
            $orderId = $conn->lastInsertId();

            // Insert order products
            $stmt = $conn->prepare("INSERT INTO order_products 
        (order_id, product_id, quantity, price, subtotal, ingredients, cup_sizes, add_ons) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            foreach ($cartItems as $item) {
                $stmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price'],
                    $item['price'] * $item['quantity'],
                    json_encode($item['ingredients']),
                    json_encode($item['cup_size']),
                    json_encode($item['add_ons'])
                ]);
            }

            // Clear the cart
            $conn->prepare("DELETE FROM cart WHERE user_id = ? AND type = ?")->execute([$user_id, $type]);

            echo json_encode(['success' => true]);
            exit;
        }

    }

    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
