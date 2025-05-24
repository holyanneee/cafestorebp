<?php
include 'config.php';

try {
    if (isset($_GET['id'])) {
        $productId = $_GET['id'];

        // Fetch product details
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            throw new Exception("Product not found");
        }

        // Fetch ingredients (assuming they're stored as comma-separated in a field)
        $ingredients = [];
        if (!empty($product['ingredients'])) {
            // Fetch the ingredient
            $ingredientIds = json_decode($product['ingredients'], true);
            $ingredientIds = is_array($ingredientIds) ? array_values($ingredientIds) : []; // Ensure numeric array
            $ingredientIds = array_unique(array_filter($ingredientIds, 'is_numeric')); // Ensure unique and numeric IDs
            
            if (empty($ingredientIds)) {
                throw new Exception("No valid ingredients found for this product");
            }

            // Prepare the SQL IN clause with placeholders
            $placeholders = implode(',', array_fill(0, count($ingredientIds), '?'));
            $ingredients_stmt = $conn->prepare("SELECT * FROM ingredients WHERE id IN ($placeholders) AND status = 'active' AND is_consumable = 1");
            $ingredients_stmt->execute($ingredientIds);
            $ingredients = $ingredients_stmt->fetchAll(PDO::FETCH_ASSOC); // Ensure associative array result
        }

        // Fetch cup sizes (stored as JSON)
        $cup_sizes = [];
        if (!empty($product['cup_sizes'])) {
            $cup_sizes = json_decode($product['cup_sizes'], true);
        }

        // Fetch available add-ons (from your add-ons category)
        $addOns = [];
        $stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Add-ons'");
        $stmt->execute();
        $addOns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'ingredients' => $ingredients,
            'cup_sizes' => $cup_sizes,
            'addOns' => $addOns,
            'base_price' => $product['price']
        ]);
        exit;
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    header("HTTP/1.0 500 Internal Server Error");
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

header("HTTP/1.0 404 Not Found");
echo "Product not found";