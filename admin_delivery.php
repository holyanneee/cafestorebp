<?php
@include 'config.php';
session_start();

// Ensure admin is logged in
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}
$type = 'coffee';

// Delete Product
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_product = $conn->prepare("DELETE FROM products WHERE id = ? ");
    $delete_product->execute([$delete_id]);
    header('location:admin_delivery.php');
    exit();
}

// Add Product
if (isset($_POST['add_product'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    // Ensure unique file name
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $unique_image_name = 'prod_' . time() . '.' . $image_ext;
    $image_path = 'uploaded_img/' . $unique_image_name;

    $cup_sizes = [];
    if (isset($_POST['cup_sizes']) && isset($_POST['cup_prices'])) {
        foreach ($_POST['cup_sizes'] as $size) {
            if (isset($_POST['cup_prices'][$size])) {
                $cup_sizes[$size] = floatval($_POST['cup_prices'][$size]);
            }
        }
    }
    $cup_sizes_json = json_encode($cup_sizes);

    if (move_uploaded_file($image_tmp_name, $image_path)) {
        $insert_product = $conn->prepare("INSERT INTO products (name, category, price, details, status, image, type, cup_sizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_product->execute([$name, $category, $price, $details, $status, $unique_image_name, $type, $cup_sizes_json]);
        header('location:admin_delivery.php');
        exit();
    }
}

// Update Product
if (isset($_POST['update_product'])) {
    $update_id = $_POST['update_id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $details = filter_var($_POST['details'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Handle image upload
    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    if (isset($_POST['cup_sizes']) && isset($_POST['cup_prices'])) {
        foreach ($_POST['cup_sizes'] as $size) {
            if (isset($_POST['cup_prices'][$size])) {
                $cup_sizes[$size] = floatval($_POST['cup_prices'][$size]);
            }
        }
    }
    $cup_sizes_json = json_encode($cup_sizes);

    if (!empty($image)) {
        $update_product = $conn->prepare("UPDATE products SET name=?, category=?, price=?, details=?, status=?, image=?, type=?, cup_sizes=? WHERE id=?");
        $update_product->execute([$name, $category, $price, $details, $status, $unique_image_name, $type, $cup_sizes_json, $update_id]);
    } else {
        $update_product = $conn->prepare("UPDATE products SET name=?, category=?, price=?, details=?, status=?, type=?, cup_sizes=? WHERE id=?");
        $update_product->execute([$name, $category, $price, $details, $status, $type, $cup_sizes_json, $update_id]);
    }

    header('location:admin_delivery.php');
    exit();
}
if (isset($_POST['save_ingredients'])) {
    $product_id = $_POST['product_id'];
    $ingredients = $_POST['ingredients'] ?? [];

    if (!empty($ingredients)) {
        // Get ingredient ids
        $placeholders = implode(',', array_fill(0, count($ingredients), '?'));
        $stmt = $conn->prepare("SELECT id, name FROM ingredients WHERE id IN ($placeholders)");
        $stmt->execute($ingredients);
        $selected = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $ingredient_ids = array_column($selected, 'id');

        // update ingredients of the product
        $update_ingredients = $conn->prepare("UPDATE products SET ingredients = ? WHERE id = ?");
        $update_ingredients->execute([implode(',', $ingredient_ids), $product_id]);


    }

    header("Location: admin_delivery.php");
    exit();
}


// Fetch all products
$show_products = $conn->prepare("SELECT * FROM products WHERE type = 'coffee' ORDER BY id DESC");
$show_products->execute();
$products = $show_products->fetchAll(PDO::FETCH_ASSOC);

// Check if ingredients are set for a product
function hasIngredients($product_id)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND ingredients IS NOT NULL");
    $stmt->execute([$product_id]);
    return $stmt->rowCount() > 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop Inventory</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/admin_style.css">

    <style>
        :root {
            --table-margin-top: 80px;
        }

        /* Adjust dropdown and search font size */
        .menu-actions select,
        .menu-actions input {
            font-size: 14px;
            /* Adjust this value as needed */
        }

        /* Make "Manage Menu" title bold */
        .menu-header h2 {
            font-weight: 700;
        }

        .menu-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', sans-serif;
            margin-left: 220px;
            margin-top: var(--table-margin-top);
        }

        .menu-actions select,
        .menu-actions input {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .add-btn {
            background-color: #ff8c1a;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }

        .add-btn:hover {
            background-color: #e67c00;
        }

        .menu-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .menu-table thead {
            background-color: #f5f5f5;
            text-align: left;
        }

        .menu-table th,
        .menu-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .item-col {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .item-col img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }

        .item-col p {
            margin: 0;
            color: #777;
            font-size: 13px;
        }

        .tag {
            background-color: #fff7e6;
            color: #ffaa00;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .edit {
            color: #e67e22;
            text-decoration: none;
            margin-right: 8px;
        }

        .delete {
            color: #e74c3c;
            text-decoration: none;
        }

        /* Modal Styles */
        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
        }

        .modal-content h3 {
            margin-bottom: 15px;
        }

        .modal-content label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
        }

        .modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .save-btn {
            background-color: #27ae60;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .cancel-btn {
            background-color: #bdc3c7;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        /* Status Tags */
        .status-tag {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .status-active {
            background-color: #e6f7e6;
            color: #27ae60;
        }

        .status-inactive {
            background-color: #fee6e6;
            color: #e74c3c;
        }

        /* Ensure modals appear on top */
        .modal {
            z-index: 1060;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            .menu-container {
                margin-left: 0;
                margin: 15px;
            }
        }

        .menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .menu-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-left: auto;
        }

        /* Adjust font size in modals */
        .modal-content {
            font-size: 14px;
            /* Adjust as needed */
        }

        /* Optionally, target specific elements */
        .modal-content label,
        .modal-content input,
        .modal-content select,
        .modal-content textarea,
        .modal-content button {
            font-size: 14px;
            /* Keep it consistent */
        }

        .edit-btn.has-ingredients {
            color: #4CAF50;
        }
    </style>
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="menu-container">
        <div class="menu-header">
            <h2>Manage Menu</h2>
            <div class="menu-actions">
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Frappe">Frappe</option>
                    <option value="Fruit Soda">Fruit Soda</option>
                    <option value="Frappe Extreme">Frappe Extreme</option>
                    <option value="Milk Tea">Milk Tea</option>
                    <option value="Fruit Tea">Fruit Tea</option>
                    <option value="Fruit Milk">Fruit Milk</option>
                    <option value="Espresso">Espresso</option>
                    <option value="Hot Non-Coffee">Hot Non-Coffee</option>
                    <option value="Iced Non-Coffee">Iced Non-Coffee</option>
                    <option value="Meal">Meal</option>
                    <option value="Snacks">Snacks</option>
                    <option value="Add-ons">Add-ons</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search menu items...">
                <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    + Add New Item
                </button>
            </div>
        </div>

        <table class="menu-table">
            <thead>
                <tr>
                    <th>ITEM</th>
                    <th>CATEGORY</th>
                    <th>PRICE</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr data-category="<?= htmlspecialchars($product['category']); ?>">
                            <td class="item-col">
                                <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>"
                                    alt="<?= htmlspecialchars($product['name']); ?>">
                                <div>
                                    <strong><?= htmlspecialchars($product['name']); ?></strong>
                                    <p><?= htmlspecialchars($product['details']); ?></p>
                                </div>
                            </td>
                            <td><span class="tag"><?= htmlspecialchars($product['category']); ?></span></td>
                            <td>₱<?= htmlspecialchars($product['price']); ?></td>
                            <td>
                                <span
                                    class="status-tag <?= $product['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                                    <?= ucfirst($product['status']) ?>
                                </span>
                            </td>
                            <td>
                                <button class="action-btn btn-update" data-bs-toggle="modal"
                                    data-bs-target="#updateProductModal-<?= $product['id'] ?>">
                                    Update
                                </button>
                                <!-- update modal -->
                                <div class="modal fade" id="updateProductModal-<?= $product['id']; ?>" tabindex="-1"
                                    aria-labelledby="updateProductModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="POST" enctype="multipart/form-data">
                                                    <input type="hidden" name="update_id" value="<?= $product['id']; ?>">

                                                    <label>Product Name</label>
                                                    <input type="text" name="name" class="form-control mb-2" required
                                                        value="<?= htmlspecialchars($product['name']); ?>">

                                                    <label>Category</label>
                                                    <select name="category" class="form-control mb-2" required>
                                                        <option value="Frappe" <?= $product['category'] === 'Frappe' ? 'selected' : ''; ?>>Frappe</option>
                                                        <option value="Fruit Soda" <?= $product['category'] === 'Fruit Soda' ? 'selected' : ''; ?>>Fruit Soda</option>
                                                        <option value="Frappe Extreme" <?= $product['category'] === 'Frappe Extreme' ? 'selected' : ''; ?>>Frappe Extreme</option>
                                                        <option value="Milk Tea" <?= $product['category'] === 'Milk Tea' ? 'selected' : ''; ?>>Milk Tea</option>
                                                        <option value="Fruit Tea" <?= $product['category'] === 'Fruit Tea' ? 'selected' : ''; ?>>Fruit Tea</option>
                                                        <option value="Fruit Milk" <?= $product['category'] === 'Fruit Milk' ? 'selected' : ''; ?>>Fruit Milk</option>
                                                        <option value="Espresso" <?= $product['category'] === 'Espresso' ? 'selected' : ''; ?>>Espresso</option>
                                                        <option value="Hot Non-Coffee" <?= $product['category'] === 'Hot Non-Coffee' ? 'selected' : ''; ?>>Hot Non-Coffee</option>
                                                        <option value="Iced Non-Coffee" <?= $product['category'] === 'Iced Non-Coffee' ? 'selected' : ''; ?>>Iced Non-Coffee</option>
                                                        <option value="Meal" <?= $product['category'] === 'Meal' ? 'selected' : ''; ?>>Meal</option>
                                                        <option value="Snacks" <?= $product['category'] === 'Snacks' ? 'selected' : ''; ?>>Snacks</option>
                                                        <option value="Add-ons" <?= $product['category'] === 'Add-ons' ? 'selected' : ''; ?>>Add-ons</option>
                                                    </select>

                                                    <label>Price</label>
                                                    <input type="number" min="0" name="price" class="form-control mb-2" required
                                                        value="<?= htmlspecialchars($product['price']); ?>">

                                                    <label>Description</label>
                                                    <textarea name="details" class="form-control mb-2"
                                                        required><?= htmlspecialchars($product['details']); ?></textarea>

                                                    <label>Status</label>
                                                    <select name="status" class="form-control mb-2" required>
                                                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>

                                                    <label>Image</label>
                                                    <input type="file" name="image" class="form-control mb-2"
                                                        accept="image/jpg, image/jpeg, image/png">
                                                    <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>"
                                                        alt="<?= htmlspecialchars($product['name']); ?>"
                                                        class="img-thumbnail mt-2" width="100">

                                                    <label>Cup Sizes and Prices</label>
                                                    <div class="mb-2">
                                                        <?php
                                                        $cup_sizes = isset($product['cup_sizes']) ? json_decode($product['cup_sizes'], true) : [];
                                                        ?>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="cup_sizes[]"
                                                                value="small" id="update_size_small"
                                                                <?= isset($cup_sizes['small']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label"
                                                                for="update_size_small">Small</label>
                                                            <input type="number" name="cup_prices[small]"
                                                                class="form-control mt-1" placeholder="Price for Small" min="0"
                                                                value="<?= $cup_sizes['small'] ?? ''; ?>">
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="cup_sizes[]"
                                                                value="regular" id="update_size_regular"
                                                                <?= isset($cup_sizes['regular']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label"
                                                                for="update_size_regular">Regular</label>
                                                            <input type="number" name="cup_prices[regular]"
                                                                class="form-control mt-1" placeholder="Price for Regular"
                                                                min="0" value="<?= $cup_sizes['regular'] ?? ''; ?>">
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="cup_sizes[]"
                                                                value="large" id="update_size_large"
                                                                <?= isset($cup_sizes['large']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label"
                                                                for="update_size_large">Large</label>
                                                            <input type="number" name="cup_prices[large]"
                                                                class="form-control mt-1" placeholder="Price for Large" min="0"
                                                                value="<?= $cup_sizes['large'] ?? ''; ?>">
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="cup_sizes[]"
                                                                value="extra_large" id="update_size_extra_large"
                                                                <?= isset($cup_sizes['extra_large']) ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="update_size_extra_large">Extra
                                                                Large</label>
                                                            <input type="number" name="cup_prices[extra_large]"
                                                                class="form-control mt-1" placeholder="Price for Extra Large"
                                                                min="0" value="<?= $cup_sizes['extra_large'] ?? ''; ?>">

                                                        </div>
                                                    </div>
                                            </div>

                                            <div class="modal-buttons">
                                                <button type="submit" class="save-btn" name="update_product">Update</button>
                                                <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>


                                <a href="admin_set_ingredients.php?id=<?= $product['id']; ?>" class="edit edit-btn 
                                <?= hasIngredients($product['id']) ? 'has-ingredients' : ''; ?>">
                                    Ingredients
                                </a>
                                <a href="admin_delivery.php?delete=<?= $product['id']; ?>" class="delete"
                                    onclick="return confirm('Delete this product?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No products added yet!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <label>Product Name</label>
                        <input type="text" name="name" class="form-control mb-2" required
                            placeholder="Enter product name">

                        <label>Category</label>
                        <select name="category" class="form-control mb-2" required>
                            <option value="" selected disabled>Select category</option>
                            <option value="Frappe">Frappe</option>
                            <option value="Fruit Soda">Fruit Soda</option>
                            <option value="Frappe Extreme">Frappe Extreme</option>
                            <option value="Milk Tea">Milk Tea</option>
                            <option value="Fruit Tea">Fruit Tea</option>
                            <option value="Fruit Milk">Fruit Milk</option>
                            <option value="Espresso">Espresso</option>
                            <option value="Hot Non-Coffee">Hot Non-Coffee</option>
                            <option value="Iced Non-Coffee">Iced Non-Coffee</option>
                            <option value="Meal">Meal</option>
                            <option value="Snacks">Snacks</option>
                            <option value="Add-ons">Add-ons</option>
                        </select>

                        <label>Price</label>
                        <input type="number" min="0" name="price" class="form-control mb-2" required
                            placeholder="Enter product price">

                        <label>Description</label>
                        <textarea name="details" class="form-control mb-2" required
                            placeholder="Enter product description"></textarea>

                        <label>Status</label>
                        <select name="status" class="form-control mb-2" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>

                        <label>Image</label>
                        <input type="file" name="image" required class="form-control mb-2"
                            accept="image/jpg, image/jpeg, image/png">

                        <label>Cup Sizes and Prices</label>
                        <div class="mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cup_sizes[]" value="small"
                                    id="size_small">
                                <label class="form-check-label" for="size_small">Small</label>
                                <input type="number" name="cup_prices[small]" class="form-control mt-1"
                                    placeholder="Price for Small" min="0">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cup_sizes[]" value="regular"
                                    id="size_regular">
                                <label class="form-check-label" for="size_regular">Regular</label>
                                <input type="number" name="cup_prices[regular]" class="form-control mt-1"
                                    placeholder="Price for Regular" min="0">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cup_sizes[]" value="large"
                                    id="size_large">
                                <label class="form-check-label" for="size_large">Large</label>
                                <input type="number" name="cup_prices[large]" class="form-control mt-1"
                                    placeholder="Price for Large" min="0">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cup_sizes[]" value="extra_large"
                                    id="extra_large">
                                <label class="form-check-label" for="extra_large">Extra Large</label>
                                <input type="number" name="cup_prices[extra_large]" class="form-control mt-1"
                                    placeholder="Price for Extra Large" min="0">
                            </div>
                        </div>

                        <div class="modal-buttons">
                            <button type="submit" class="save-btn" name="add_product">Save</button>
                            <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="ingredientsModal" tabindex="-1" aria-labelledby="ingredientsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Ingredients</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" id="ingredients_product_id">
                        <div class="form-group">
                            <label>Select Ingredients:</label>
                            <?php
                            $stmt = $conn->prepare("SELECT id, name FROM ingredients ORDER BY name ASC");
                            $stmt->execute();
                            $ingredientOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($ingredientOptions as $ingredient) {
                                echo '<div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ingredients[]" value="' . $ingredient['id'] . '" id="ing_' . $ingredient['id'] . '">
                        <label class="form-check-label" for="ing_' . $ingredient['id'] . '">' . htmlspecialchars($ingredient['name']) . '</label>
                      </div>';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="save_ingredients" class="edit edit-btn">Save</button>
                        <button type="button" class="delete" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Populate update modal with product data
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    document.getElementById("update_id").value = this.dataset.id;
                    document.getElementById("update_name").value = this.dataset.name;
                    document.getElementById("update_category").value = this.dataset.category;
                    document.getElementById("update_price").value = this.dataset.price;
                    document.getElementById("update_details").value = this.dataset.details;
                    document.getElementById("update_status").value = this.dataset.status;
                    document.getElementById("update_image").src = this.dataset.image;
                });
            });

            // Category filter functionality
            document.getElementById("categoryFilter").addEventListener("change", function () {
                const selectedCategory = this.value;
                const rows = document.querySelectorAll(".menu-table tbody tr");

                rows.forEach(row => {
                    if (selectedCategory === "" || row.dataset.category === selectedCategory) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });

            //Edit ingeredient
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    // For update modal
                    if (this.dataset.id) {
                        document.getElementById("update_id").value = this.dataset.id;
                        document.getElementById("update_name").value = this.dataset.name;
                        document.getElementById("update_category").value = this.dataset.category;
                        document.getElementById("update_price").value = this.dataset.price;
                        document.getElementById("update_details").value = this.dataset.details;
                        document.getElementById("update_status").value = this.dataset.status;
                        document.getElementById("update_image").src = this.dataset.image;
                    }

                    // For ingredients modal
                    if (this.dataset.productId) {
                        document.getElementById("ingredients_product_id").value = this.dataset.productId;
                    }
                });
            });

            // Search functionality
            document.getElementById("searchInput").addEventListener("input", function () {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll(".menu-table tbody tr");

                rows.forEach(row => {
                    const itemName = row.querySelector(".item-col strong").textContent.toLowerCase();
                    const itemDesc = row.querySelector(".item-col p").textContent.toLowerCase();

                    if (itemName.includes(searchTerm) || itemDesc.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });
        });
    </script>
</body>

</html>