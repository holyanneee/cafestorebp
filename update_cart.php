<?php
@include 'config.php';
session_start();

header('Content-Type: application/json');
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $current_store = $_SESSION['store'] ?? null;
    $type = ($current_store === 'kape_milagrosa') ? 'coffee' : 'religious';

    if (!$user_id) {
        ob_clean(); // ADD THIS to ensure clean output buffer
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // DELETE CART ITEM
        if ($_POST['action'] === 'delete' && isset($_POST['cart_id'])) {
            $cart_id = filter_var($_POST['cart_id'], FILTER_SANITIZE_NUMBER_INT);
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->execute([$cart_id, $user_id]);

            echo json_encode(['success' => true]);
            exit;
        }

        // UPDATE CART ITEM
        if (isset($_POST['id'])) {
            $cart_id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
            $quantity = filter_var($_POST['quantity'], FILTER_SANITIZE_NUMBER_INT);
            $cup_size = $_POST['cup_size'] ?? null;
            $subtotal = filter_var($_POST['subtotal'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $ingredients = $_POST['ingredients'] ?? null;
            $add_ons = $_POST['add_ons'] ?? null;
            $special_instructions = filter_var($_POST['special_instructions'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            foreach (['cup_size' => $cup_size, 'ingredients' => $ingredients, 'add_ons' => $add_ons] as $key => $value) {
                if ($value !== null && json_decode($value, true) === null && json_last_error() !== JSON_ERROR_NONE) {
                    ob_clean(); // ADD THIS to ensure clean output buffer
                    echo json_encode(['success' => false, 'message' => "Invalid JSON in $key"]);
                    exit;
                }
            }

            $stmt = $conn->prepare("UPDATE cart 
                SET quantity = ?, cup_size = ?, subtotal = ?, ingredients = ?, add_ons = ?, special_instruction = ?
                WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $quantity,
                $cup_size,
                $subtotal,
                $ingredients,
                $add_ons,
                $special_instructions,
                $cart_id,
                $user_id
            ]);
            ob_clean(); // ADD THIS to ensure clean output buffer

            echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
            exit;
        }

        // CHECKOUT
        if ($_POST['action'] === 'checkout') {
            $name = $_POST['name'];
            $number = $_POST['number'];
            $email = $_POST['email'];
            $method = $_POST['method'];
            $address = $_POST['address'];

            // Get cart
            $stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image 
                FROM cart 
                JOIN products ON cart.product_id = products.id 
                WHERE cart.user_id = ? AND cart.type = ?");
            $stmt->execute([$user_id, $type]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo $cartItems;

            if (count($cartItems) === 0) {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                exit;
            }

            // Insert into orders
            $stmt = $conn->prepare("INSERT INTO orders 
                (user_id, name, number, email, method, address, type) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $name,
                $number,
                $email,
                $method,
                $address,
                $type
            ]);
            $orderId = $conn->lastInsertId();

            // Insert into order_products
            if ($type === 'coffee') {
                $query = "INSERT INTO order_products 
                    (order_id, product_id, quantity, price, subtotal, ingredients, cup_sizes, add_ons) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            } else {
                $query = "INSERT INTO order_products 
                    (order_id, product_id, quantity, price, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";
            }
            $stmt = $conn->prepare($query);

            foreach ($cartItems as $item) {
                if ($type === 'coffee') {
                    $cup_size = json_decode($item['cup_size'], true) ?: [];
                    $ingredients = json_decode($item['ingredients'], true) ?: [];
                    $add_ons = json_decode($item['add_ons'], true) ?: [];

                    $stmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price'],
                        $item['subtotal'],
                        json_encode($ingredients, JSON_UNESCAPED_UNICODE),
                        json_encode($cup_size, JSON_UNESCAPED_UNICODE),
                        json_encode($add_ons, JSON_UNESCAPED_UNICODE)
                    ]);
                } else {
                    $stmt->execute([
                        $orderId,
                        $item['product_id'],
                        $item['quantity'],
                        $item['price'],
                        $item['subtotal']
                    ]);
                }
            }

            // Clear cart
            $conn->prepare("DELETE FROM cart WHERE user_id = ? AND type = ?")->execute([$user_id, $type]);
            ob_clean(); // ADD THIS to ensure clean output buffer
            echo json_encode(['success' => true, 'message' => 'Checkout successful']);
            exit;
        }
    }

} catch (Throwable $e) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    ob_end_flush();
}
