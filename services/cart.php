<?php
declare(strict_types=1);

// Setup
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: application/json');

require __DIR__ . '/../config.php';
session_start();

$user_id = $_SESSION['user_id'] ?? 0;
$action = $_POST['action'] ?? '';
$cart_id = isset($_POST['cart_id']) ? (int) $_POST['cart_id'] : 0;
$product_id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;



// Basic validation
if (!$conn) {
    exit(json_encode(['status' => 'error', 'message' => 'Database connection error']));
}

if (!$user_id) {
    exit(json_encode(['status' => 'error', 'message' => 'Please log in first']));
}

// Helper functions
function jsonError(string $message): never
{
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

function jsonSuccess(string $message): never
{
    echo json_encode(['status' => 'success', 'message' => $message]);
    exit;
}


if ($action === 'add_to_cart' && $product_id > 0) {
    try {
        // Fetch product
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product)
            jsonError('Product not found');

        // Check existing cart entry
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM cart WHERE product_id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$product_id, $user_id]);
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem['quantity'] + 1;
            $newSubtotal = $product['price'] * $newQuantity;

            $upd = $GLOBALS['conn']->prepare(
                "UPDATE cart SET quantity = ?, subtotal = ? WHERE product_id = ? AND user_id = ?"
            );
            $upd->execute([$newQuantity, $newSubtotal, $product_id, $user_id]);

            jsonSuccess('Quantity updated in cart!');
        }

        // Remove from wishlist if exists
        $GLOBALS['conn']->prepare("DELETE FROM wishlist WHERE product_id = ? AND user_id = ?")
            ->execute([$product_id, $user_id]);

        // Coffee-type customization
        if ($product['type'] === 'coffee') {
            $ingredients = [];
            $ingredientIds = json_decode($product['ingredients'] ?? '[]', true);

            if (!empty($ingredientIds)) {
                $placeholders = implode(',', array_fill(0, count($ingredientIds), '?'));
                $ingStmt = $GLOBALS['conn']->prepare("SELECT id, name FROM ingredients WHERE id IN ($placeholders)");
                $ingStmt->execute($ingredientIds);

                foreach ($ingStmt->fetchAll(PDO::FETCH_ASSOC) as $ing) {
                    $ingredients[$ing['id']] = [
                        'name' => $ing['name'],
                        'level' => 'regular',
                    ];
                }
            }

            // Cup sizes
            $cupSizes = json_decode($product['cup_sizes'] ?? '{}', true);

            // Check if there is a "regular" size, otherwise choose "small" or the first available
            if (isset($cupSizes['regular'])) {
                $cup_size = 'regular';
            } elseif (isset($cupSizes['small'])) {
                $cup_size = 'small';
            } elseif (!empty($cupSizes)) {
                // fallback to the first key if no regular/small exists
                $cup_size = array_key_first($cupSizes);
            } else {
                // if no cup sizes at all
                $cup_size = null;
            }

            $cup_price = $cup_size && isset($cupSizes[$cup_size]) ? $cupSizes[$cup_size] : 0;
            $subtotal = $product['price'] + $cup_price;


            $insert = $GLOBALS['conn']->prepare(
                "INSERT INTO cart (user_id, product_id, quantity, ingredients, cup_size, subtotal, type)
                 VALUES (?, ?, 1, ?, ?, ?, 'coffee')"
            );
            $insert->execute([
                $user_id,
                $product_id,
                json_encode($ingredients),
                json_encode(['size' => $cup_size, 'price' => $cup_price]),
                $subtotal
            ]);
        } else {
            // Non-coffee items
            $insert = $GLOBALS['conn']->prepare(
                "INSERT INTO cart (user_id, product_id, subtotal, quantity, type)
                 VALUES (?, ?, ?, 1, ?)"
            );
            $insert->execute([$user_id, $product_id, $product['price'], $product['type'] ?? 'other']);
        }

        jsonSuccess('Added to cart!');
    } catch (PDOException $e) {
        jsonError($e->getMessage());
    }
}




if ($action == 'customize_cart_item') {
    if (empty($cart_id) || $cart_id <= 0) {
        jsonError('Invalid cart ID.');
    }

    try {
        // Fetch cart item
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$cart_id, $user_id]);
        $cart = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cart) {
            jsonError('Cart item not found or unauthorized.');
        }

        // Fetch product info
        $product_id = $cart['product_id'];
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            jsonError('Product not found.');
        }

        // Cup size handling
        $cupSizes = json_decode($product['cup_sizes'] ?? '{}', true);
        $cup_size = $_POST['cup-size'] ?? 'regular';
        if (!isset($cupSizes[$cup_size])) {
            $cup_size = isset($cupSizes['regular']) ? 'regular' : array_key_first($cupSizes);
        }
        $cup_price = (float) ($cupSizes[$cup_size] ?? 0);

        // Ingredients update
        $ingredients = json_decode($cart['ingredients'] ?? '[]', true);
        foreach ($ingredients as &$ingredient) {
            $key = 'ingredient-' . strtolower($ingredient['name']);
            $level = strtolower($_POST[$key] ?? 'regular');
            if (in_array($level, ['less', 'regular', 'extra'])) {
                $ingredient['level'] = $level;
            }
        }

        // Add-ons 
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Add-ons'");
        $stmt->execute();
        $available_addons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selected_addons = [];
        foreach ($available_addons as $add_on) {
            $key = 'add-on-' . $add_on['id'];
            if (isset($_POST[$key])) {
                $selected_addons[$add_on['id']] = [
                    'name' => $add_on['name'],
                    'price' => (float) $add_on['price']
                ];
            }
        }

        // Quantity & special instructions
        $quantity = max(1, (int) ($_POST['quantity'] ?? $cart['quantity']));
        $special_instruction = trim($_POST['special-instruction'] ?? $cart['special_instruction'] ?? '');

        // Calculate subtotal
        $base_price = (float) $product['price'];
        $addons_total = array_sum(array_column($selected_addons, 'price'));
        $subtotal = ($base_price + $cup_price + $addons_total) * $quantity;

        // Update cart
        $update = $GLOBALS['conn']->prepare("
            UPDATE cart 
            SET cup_size = ?, 
                ingredients = ?, 
                add_ons = ?, 
                quantity = ?, 
                special_instruction = ?, 
                subtotal = ? 
            WHERE id = ? AND user_id = ?
        ");
        $update->execute([
            json_encode(['size' => $cup_size, 'price' => $cup_price], JSON_UNESCAPED_UNICODE),
            json_encode($ingredients, JSON_UNESCAPED_UNICODE),
            json_encode($selected_addons, JSON_UNESCAPED_UNICODE),
            $quantity,
            $special_instruction,
            $subtotal,
            $cart_id,
            $user_id
        ]);

        jsonSuccess('Cart item updated successfully.');

    } catch (PDOException $e) {
        jsonError($e->getMessage());
    }
}

if ($action == 'update_quantity') {
    if ($cart_id <= 0)
        jsonError('Invalid cart ID.');

    $new_quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
    if ($new_quantity < 1)
        jsonError('Quantity must be at least 1.');

    try {
        $verify = $GLOBALS['conn']->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1");
        $verify->execute([$cart_id, $user_id]);
        $item = $verify->fetch(PDO::FETCH_ASSOC);

        if (!$item)
            jsonError('Cart item not found or unauthorized.');

        // Fetch product price
        $stmt = $GLOBALS['conn']->prepare("SELECT price FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$item['product_id']]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product)
            jsonError('Product not found.');

        // Calculate new subtotal
        $base_price = (float) $product['price'];
        $cup_size_info = json_decode($item['cup_size'] ?? '{}', true);
        $cup_price = isset($cup_size_info['price']) ? (float) $cup_size_info['price'] : 0;

        $addons = json_decode($item['add_ons'] ?? '{}', true);
        $addons_total = array_sum(array_column($addons, 'price'));

        $new_subtotal = ($base_price + $cup_price + $addons_total) * $new_quantity;

        // Update cart
        $upd = $GLOBALS['conn']->prepare("UPDATE cart SET quantity = ?, subtotal = ? WHERE id = ? AND user_id = ?");
        $upd->execute([$new_quantity, $new_subtotal, $cart_id, $user_id]);

        jsonSuccess('Quantity updated successfully.');
    } catch (PDOException $e) {
        jsonError($e->getMessage());
    }
}

if ($action === 'remove_from_cart') {
    if ($cart_id <= 0)
        jsonError('Invalid cart ID.');

    try {
        $verify = $GLOBALS['conn']->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1");
        $verify->execute([$cart_id, $user_id]);
        $item = $verify->fetch(PDO::FETCH_ASSOC);

        if (!$item)
            jsonError('Cart item not found or unauthorized.');

        $del = $GLOBALS['conn']->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $del->execute([$cart_id, $user_id]);

        jsonSuccess('Item removed from cart.');
    } catch (PDOException $e) {
        jsonError($e->getMessage());
    }
}



// Default fallback
jsonError('Invalid action or missing parameters');
