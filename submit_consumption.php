<?php
@include 'config.php';
session_start();

// var_dump($_POST['ingredients']);
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredients'])) {
    try {
        $conn->beginTransaction();

        foreach ($_POST['ingredients'] as $product_index => $ingredient_inputs) {
            foreach ($ingredient_inputs as $ingredient_name => $used_amount) {
                $used_amount = floatval($used_amount);
                if ($used_amount <= 0) continue;

                // Get product name from POST indirectly
                // (Optional: you can pass hidden inputs with product_name if needed)

                // Step 1: Find the ingredient ID via name (optional optimization)
                $stmt = $conn->prepare("SELECT id, stock FROM ingredients WHERE name = ?");
                $stmt->execute([$ingredient_name]);
                $ingredient = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$ingredient) continue;

                $ingredient_id = $ingredient['id'];
                $current_stock = floatval($ingredient['stock']);

                if ($used_amount > $current_stock) {
                    throw new Exception("Insufficient stock for ingredient: " . htmlspecialchars($ingredient_name));
                }

                // Step 2: Subtract used amount from stock
                $new_stock = $current_stock - $used_amount;
                $update_stmt = $conn->prepare("UPDATE ingredients SET stock = ? WHERE id = ?");
                $update_stmt->execute([$new_stock, $ingredient_id]);

                // Step 3: Update payment_status of the order
                $order_id = $_POST['order_id'] ?? null;
                if ($order_id) {
                    $update_status_stmt = $conn->prepare("UPDATE orders SET payment_status = 'completed' WHERE id = ?");
                    $update_status_stmt->execute([$order_id]);
                }

            }
        }

        $conn->commit();
        header("Location: barista_ongoing_order.php?status=success");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
        // Optionally redirect to error page
    }
} else {
    echo "Invalid request.";
}
?>