<?php
@include 'config.php';
session_start();

// Check if the admin is logged in
$admin_id = $_SESSION['barista_id'];
if (!isset($admin_id)) {
    header('location:login.php');
    exit();
}

// Get the order_id from the URL
$order_id = $_GET['order_id'];

// Fetch order details using PDO
$order_sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($order_sql);
$stmt->bindParam(1, $order_id, PDO::PARAM_INT);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if order exists
if ($order) {
    // Order details
    $customer_name = $order['name'];
    $number = $order['number'];
    $email = $order['email'];
    $method = $order['method'];
    $address = $order['address'];
    $total_price = $order['total_price'];
    $cashier = $order['cashier'];
    $order_date = $order['placed_on']; // assuming there is a created_at field
    $total_products = explode(",", $order['total_products']); // The total products field

    $products = [];
    foreach ($total_products as $product) {
        // Clean up spaces and split product name and quantity
        preg_match('/(.*)\s?\(\s*(\d+)\s*\)/', trim($product), $matches); // Adjusted regex pattern to handle spaces more effectively
        if (count($matches) == 3) {
            $product_name = trim($matches[1]); // Remove any extra spaces around the name
            $quantity = (int) $matches[2]; // Ensure quantity is an integer

            // Fetch product details
            $product_stmt = $conn->prepare("SELECT * FROM products WHERE name = ? LIMIT 1");
            $product_stmt->bindParam(1, $product_name, PDO::PARAM_STR);
            $product_stmt->execute();
            $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

            // If product found, fetch ingredients
            if ($product) {
                $product_id = $product['id'];

                // Fetch ingredients from barista_inventory
                $inventory_stmt = $conn->prepare("SELECT ingredients_names FROM barista_inventory WHERE product_id = ?");
                $inventory_stmt->bindParam(1, $product_id, PDO::PARAM_INT);
                $inventory_stmt->execute();
                $inventory = $inventory_stmt->fetch(PDO::FETCH_ASSOC);

                $ingredients = [];
                if ($inventory) {
                    $ingredients = array_map('trim', explode(',', $inventory['ingredients_names']));
                }

                // Add the product to the array multiple times based on its quantity
                for ($i = 0; $i < $quantity; $i++) {
                    $products[] = [
                        'name' => $product['name'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'ingredients' => $ingredients
                    ];
                }
            }
        }
    }
} else {
    echo "Order not found.";
}

// Close the PDO connection (not needed in PDO but can be done)
$conn = null;
?>





<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Barista Ingredient Inventory</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f9f9f9;
      padding: 20px;
      display: flex;
      justify-content: center;
    }

    .container {
      max-width: 900px;
      width: 100%;
    }

    .order-box {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      margin-bottom: 20px;
    }

    .order-box h2 {
      margin-top: 0;
      margin-bottom: 15px;
    }

    .order-details {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px 30px;
    }

    .order-details p {
      margin: 5px 0;
    }

    .product-list {
      max-height: 500px;
      overflow-y: auto;
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 5px rgba(0,0,0,0.1);
      margin-top: 20px;
    }

    .product-item {
      display: flex;
      flex-direction: column;
      border-bottom: 1px solid #ccc;
      padding: 15px 0;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .product-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .product-top {
      display: flex;
      gap: 20px;
      align-items: center;
    }

    .product-top img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .product-top div {
      flex: 1;
    }

    .product-top h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      color: #333;
    }

    .product-top p {
      margin: 5px 0;
      color: #555;
      font-size: 16px;
    }

    .ingredient-inputs {
      margin-top: 10px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      gap: 10px;
    }

    .ingredient-inputs label {
      display: flex;
      flex-direction: column;
      font-size: 14px;
      color: #444;
    }

    input[type="number"] {
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-top: 5px;
      font-size: 14px;
    }

    .submit-btn {
      margin-top: 20px;
      padding: 10px 20px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }

    .submit-btn:hover {
      background: #218838;
    }

    .cancel-btn {
      margin-top: 20px;
      padding: 10px 20px;
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: background-color 0.3s ease;
      text-align: center;
    }

    .cancel-btn:hover {
      background: #c82333;
    }

    @media (max-width: 768px) {
      .order-details {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>

<div class="container">

<!-- Order Details -->
<div class="order-box">
  <h2>Order Details</h2>
  <div class="order-details">
    <p><strong>Customer Name:</strong> <?= htmlspecialchars($customer_name) ?></p>
    <p><strong>Contact Number:</strong> <?= htmlspecialchars($number) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($method) ?></p>
    <p><strong>Delivery Address:</strong> <?= htmlspecialchars($address) ?></p>
    <p><strong>Total Price:</strong> ₱<?= number_format($total_price, 2) ?></p>
    <p><strong>Date of Order:</strong> <?= htmlspecialchars($order_date) ?></p>
    <p><strong>Cashier Name:</strong> <?= htmlspecialchars($cashier) ?></p>
  </div>
</div>

<!-- Products and Ingredients -->
<form method="POST" action="submit_consumption.php">
  <div class="product-list">
  <input type="hidden" name="order_id" value="<?= htmlspecialchars($order_id) ?>">
    <?php foreach ($products as $key => $product): ?>
      <div class="product-item">
        <div class="product-top">
          <img src="uploaded_img/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
          <div>
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p>₱<?= number_format($product['price'], 2) ?></p>
          </div>
        </div>
        <div class="ingredient-inputs">
          <?php foreach ($product['ingredients'] as $ingredient): ?>
            <label><?= htmlspecialchars($ingredient) ?>
              <input type="number" name="ingredients[<?= $key ?>][<?= htmlspecialchars($ingredient) ?>]" step="0.1" min="0">
            </label>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>

  <!-- Action Buttons -->
  <div style="display: flex; gap: 10px; margin-top: 20px;">
    <a href="barista_ongoing_order.php" class="cancel-btn">Cancel</a>
    <button type="submit" class="submit-btn">Submit Consumption</button>
  </div>
</form>
</div>

</body>
</html>
