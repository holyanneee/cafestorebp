<?php
@include 'config.php';
session_start();

header('Content-Type: application/json');

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

try {
    $user_id = $_SESSION['user_id'] ?? null;
    $current_store = $_SESSION['store'] ?? null;
    $type = ($current_store === 'kape_milagrosa') ? 'coffee' : 'online';
    if (!$user_id) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // DELETE SINGLE ITEM
        if ($_POST['action'] === 'delete' && isset($_POST['wishlist_id'])) {
            $wishlist_id = filter_var($_POST['wishlist_id'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ? AND type = ?");
            $stmt->execute([$wishlist_id, $user_id, $type]);

            echo json_encode(['success' => true]);
            exit;
        }

        // DELETE ALL ITEMS
        if ($_POST['action'] === 'delete_all') {
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND type = ?");
            $stmt->execute([$user_id, $type]);

            echo json_encode(['success' => true]);
            exit;
        }

        // MOVE SINGLE ITEM TO CART
        if ($_POST['action'] === 'store' && isset($_POST['wishlist_id'])) {
            $wishlist_id = filter_var($_POST['wishlist_id'], FILTER_SANITIZE_NUMBER_INT);

            // Get product info from wishlist
            $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE id = ? AND user_id = ? AND type = ?");
            $stmt->execute([$wishlist_id, $user_id, $type]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($item) {
                $product_id = $item['product_id'];

                // Check if already in cart
                $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND type = ?");
                $stmt->execute([$user_id, $product_id, $type]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                //Fetch the ingredients for product
                $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                $select_product->execute([$product_id]);
                $product = $select_product->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Increase quantity
                    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
                    $stmt->execute([$existing['id']]);

                    // update the subtotal
                    $update_subtotal = $conn->prepare("UPDATE cart SET subtotal = ? WHERE id = ?");
                    $subtotal = $product['price'] * ($existing['quantity'] + 1); // Assuming price is in the product table
                    $update_subtotal->execute([$subtotal, $existing['id']]);

                } else {
                    //Fetch the ingredients for product
                    $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                    $select_product->execute([$product_id]);
                    $product = $select_product->fetch(PDO::FETCH_ASSOC);
                    if ($type === 'coffee') {
                        // fetch the ingredients
                        $ingredients = [];
                        foreach (json_decode($product['ingredients']) as $ingredient_id) {
                            $product_ingredients = $conn->prepare("SELECT * FROM `ingredients` WHERE id = ?");
                            $product_ingredients->execute([$ingredient_id]);
                            while ($ingredient = $product_ingredients->fetch(PDO::FETCH_ASSOC)) {
                                $ingredients[$ingredient['id']] = [
                                    'name' => $ingredient['name'],
                                    'level' => 'Regular'
                                ];
                            }
                        }

                        // cup_size(json column)
                        $cup_size = 'Small';

                        $cup_sizes = json_decode($product['cup_sizes'], true); // Decode JSON to array
                        if (isset($cup_sizes['regular'])) {
                            $cup_size = 'Regular';
                        }
                        $cup_price = $cup_sizes[strtolower($cup_size)] ?? 0; // Use null coalescing operator for safety


                        $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, product_id, quantity, ingredients, cup_size, subtotal, type) VALUES(?, ?, 1, ?, ?, ?, 'coffee')");
                        $insert_cart->execute([
                            $user_id,
                            $product_id,
                            json_encode($ingredients),
                            json_encode([
                                'size' => $cup_size,
                                'price' => $cup_price
                            ]),
                            ($product['price'] + $cup_price) * 1
                        ]);

                    } else {
                        // Insert new cart item (basic example)
                        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, type) VALUES (?, ?, ?, 'online')");
                        $stmt->execute([$user_id, $product_id, 1]);
                    }
                }

                // Optionally delete from wishlist
                $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ? AND type = ?");
                $stmt->execute([$wishlist_id, $user_id, $type]);

                echo json_encode(['success' => true]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Item not found']);
                exit;
            }
        }

        // MOVE ALL ITEMS TO CART
        if ($_POST['action'] === 'store_all') {
            $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ? AND type = ?");
            $stmt->execute([$user_id, $type]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($items as $item) {
                $product_id = $item['product_id'];

                $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ? AND type = ?");
                $stmt->execute([$user_id, $product_id, $type]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existing) {
                    // Increase quantity
                    $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
                    $stmt->execute([$existing['id']]);

                    // update the subtotal
                    $update_subtotal = $conn->prepare("UPDATE cart SET subtotal = ? WHERE id = ?");
                    $subtotal = $product['price'] * ($existing['quantity'] + 1); // Assuming price is in the product table
                    $update_subtotal->execute([$subtotal, $existing['id']]);

                } else {
                    //Fetch the ingredients for product
                    $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                    $select_product->execute([$product_id]);
                    $product = $select_product->fetch(PDO::FETCH_ASSOC);
                    if ($type === 'coffee') {
                        // fetch the ingredients
                        $ingredients = [];
                        foreach (json_decode($product['ingredients']) as $ingredient_id) {
                            $product_ingredients = $conn->prepare("SELECT * FROM `ingredients` WHERE id = ?");
                            $product_ingredients->execute([$ingredient_id]);
                            while ($ingredient = $product_ingredients->fetch(PDO::FETCH_ASSOC)) {
                                $ingredients[$ingredient['id']] = [
                                    'name' => $ingredient['name'],
                                    'level' => 'Regular'
                                ];
                            }
                        }

                        // cup_size(json column)
                        $cup_size = 'Small';

                        $cup_sizes = json_decode($product['cup_sizes'], true); // Decode JSON to array
                        if (isset($cup_sizes['regular'])) {
                            $cup_size = 'Regular';
                        }
                        $cup_price = $cup_sizes[strtolower($cup_size)] ?? 0; // Use null coalescing operator for safety


                        $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, product_id, quantity, ingredients, cup_size, subtotal, type) VALUES(?, ?, 1, ?, ?, ?, 'coffee')");
                        $insert_cart->execute([
                            $user_id,
                            $product_id,
                            json_encode($ingredients),
                            json_encode([
                                'size' => $cup_size,
                                'price' => $cup_price
                            ]),
                            ($product['price'] + $cup_price) * 1
                        ]);

                    } else {
                        // Insert new cart item (basic example)
                        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, type) VALUES (?, ?, ?, 'online')");
                        $stmt->execute([$user_id, $product_id, 1]);
                    }
                }
            }

            // Optionally delete all from wishlist
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND type = ?");
            $stmt->execute([$user_id, $type]);

            echo json_encode(['success' => true]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'message' => 'Invalid request']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
