<?php
@include 'config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    echo "No product selected.";
    exit();
}

// Fetch product info
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all ingredients
$ingredients_stmt = $conn->prepare("SELECT * FROM ingredients");
$ingredients_stmt->execute();
$all_ingredients = $ingredients_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch already selected ingredients
$selected_stmt = $conn->prepare("SELECT ingredients_id FROM barista_inventory WHERE product_id = ?");
$selected_stmt->execute([$product_id]);
$selected_result = $selected_stmt->fetch(PDO::FETCH_ASSOC);
$selected_ids = [];
if ($selected_result && !empty($selected_result['ingredients_id'])) {
    $selected_ids = array_map('intval', explode(',', $selected_result['ingredients_id']));
}

// Handle saving
if (isset($_POST['save_ingredients'])) {
    $product_id = $_POST['product_id'];
    $ingredients = $_POST['ingredients'] ?? '';
    $ingredient_ids = array_filter(array_map('intval', explode(',', $ingredients)));

    if (!empty($ingredient_ids)) {
        // Get ingredient names
        $placeholders = implode(',', array_fill(0, count($ingredient_ids), '?'));
        $stmt = $conn->prepare("SELECT id, name FROM ingredients WHERE id IN ($placeholders)");
        $stmt->execute($ingredient_ids);
        $selected = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ids_str = implode(', ', array_column($selected, 'id'));
        $names_str = implode(', ', array_column($selected, 'name'));

        // Check if record exists
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM barista_inventory WHERE product_id = ?");
        $check_stmt->execute([$product_id]);
        $exists = $check_stmt->fetchColumn();

        if ($exists) {
            $update = $conn->prepare("UPDATE barista_inventory SET ingredients_id = ?, ingredients_names = ? WHERE product_id = ?");
            $update->execute([$ids_str, $names_str, $product_id]);
        } else {
            $insert = $conn->prepare("INSERT INTO barista_inventory (product_id, ingredients_id, ingredients_names) VALUES (?, ?, ?)");
            $insert->execute([$product_id, $ids_str, $names_str]);
        }
    }

    header("Location: admin_delivery.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Ingredients</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f9f9f9;
    }
    .container {
      max-width: 700px;
      margin: 0 auto;
      padding: 20px;
    }
    .back-button {
      display: inline-block;
      margin-bottom: 20px;
      background-color: #ddd;
      color: #333;
      padding: 8px 12px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
    }
    .product-section {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 20px;
      border-bottom: 2px solid #ccc;
      padding-bottom: 20px;
    }
    .product-image {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid #ddd;
    }
    .product-info {
      flex-grow: 1;
    }
    .product-info h2, .product-info p {
      margin: 5px 0;
    }
    .selected-ingredients {
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      min-height: 60px;
      background-color: #fff;
      flex-basis: 100%;
    }
    .ingredients-section {
      margin-top: 30px;
    }
    .ingredient-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px;
      border-bottom: 1px solid #eee;
    }
    .ingredient-item button {
      padding: 5px 10px;
      background-color: #4CAF50;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .ingredient-item button:hover {
      background-color: #45a049;
    }
    .ingredient-tag {
      display: inline-block;
      background-color: #e0f7fa;
      color: #00796b;
      margin: 5px;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
      cursor: pointer;
    }
    .ingredient-tag:hover {
      background-color: #b2ebf2;
    }
    .submit-btn {
      margin-top: 20px;
      padding: 10px 20px;
      background-color: #2196F3;
      color: white;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }
    .submit-btn:hover {
      background-color: #0b7dda;
    }
  </style>
</head>
<body>

  <div class="container">
    <a href="admin_delivery.php" class="back-button">&larr; Back</a>

    <form method="POST">
      <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
      <input type="hidden" name="ingredients" id="ingredientsHidden" />

      <div class="product-section">
        <img src="uploaded_img/<?= $product['image']; ?>" alt="Product Image" class="product-image" />
        <div class="product-info">
          <h2><?= htmlspecialchars($product['name']) ?></h2>
          <p>Category: <?= htmlspecialchars($product['category']) ?></p>
          <p>Price: $<?= number_format($product['price'], 2) ?></p>
        </div>
        <div class="selected-ingredients" id="selectedIngredients">
          <strong>Selected Ingredients:</strong>
        </div>
      </div>

      <div class="ingredients-section">
        <h3>Ingredients List</h3>
        <?php foreach ($all_ingredients as $ingredient): ?>
          <div class="ingredient-item">
            <span><?= htmlspecialchars($ingredient['name']) ?></span>
            <button type="button" onclick="selectIngredient(<?= $ingredient['id'] ?>, '<?= htmlspecialchars($ingredient['name'], ENT_QUOTES) ?>')">
              Select Ingredient
            </button>
          </div>
        <?php endforeach; ?>
      </div>

      <button type="submit" name="save_ingredients" class="submit-btn">Save Ingredients</button>
    </form>
  </div>

  <script>
    const selectedBox = document.getElementById('selectedIngredients');
    const ingredientsHidden = document.getElementById('ingredientsHidden');
    const selectedIds = new Set();

    function updateHiddenField() {
      ingredientsHidden.value = [...selectedIds].join(',');
    }

    function selectIngredient(id, name) {
      if (selectedIds.has(id)) return;
      selectedIds.add(id);
      updateHiddenField();

      const tag = document.createElement('span');
      tag.className = 'ingredient-tag';
      tag.textContent = name;
      tag.setAttribute('data-id', id);
      tag.onclick = () => removeIngredient(id, tag);
      selectedBox.appendChild(tag);
    }

    function removeIngredient(id, tagElement) {
      selectedIds.delete(id);
      updateHiddenField();
      selectedBox.removeChild(tagElement);
    }

    // Pre-fill selected ingredients from PHP
    <?php foreach ($selected_ids as $id): ?>
      <?php
        $name_stmt = $conn->prepare("SELECT name FROM ingredients WHERE id = ?");
        $name_stmt->execute([$id]);
        $name = $name_stmt->fetchColumn();
      ?>
      selectIngredient(<?= $id ?>, '<?= htmlspecialchars($name, ENT_QUOTES) ?>');
    <?php endforeach; ?>
  </script>

</body>
</html>
