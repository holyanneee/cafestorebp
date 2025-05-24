
<?php
@include 'config.php';
session_start();

// Ensure admin is logged in
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Delete Ingredient
if (isset($_GET['delete_ingredient'])) {
    $delete_id = $_GET['delete_ingredient'];
    $delete_ingredient = $conn->prepare("DELETE FROM ingredients WHERE id = ?");
    $delete_ingredient->execute([$delete_id]);
    header('location:admin_ingredients.php');
    exit();
}

// Add Ingredient
if (isset($_POST['add_ingredient'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $unit = filter_var($_POST['unit'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_consumable = filter_var($_POST['is_consumable'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    $insert_ingredient = $conn->prepare("INSERT INTO ingredients (name, stock, unit, status, is_consumable) VALUES (?, ?, ?, ?, ?)");
    $insert_ingredient->execute([$name, $stock, $unit, $status, $is_consumable]);
    header('location:admin_ingredients.php');
    exit();
}

// Update Ingredient
if (isset($_POST['update_ingredient'])) {
    $update_id = $_POST['update_id'];
    $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $unit = filter_var($_POST['unit'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $status = filter_var($_POST['status'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $is_consumable = filter_var($_POST['is_consumable'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    $update_ingredient = $conn->prepare("UPDATE ingredients SET name=?, stock=?, unit=?, status=?, is_consumable=? WHERE id=?");
    $update_ingredient->execute([$name, $stock, $unit, $status, $is_consumable, $update_id]);

    header('location:admin_ingredients.php');
    exit();
}

// Fetch all ingredients
$show_ingredients = $conn->prepare("SELECT * FROM ingredients");
$show_ingredients->execute();
$ingredients = $show_ingredients->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>

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
    </style>
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="menu-container">
        <div class="menu-header">
            <h2>Manage Ingredients</h2>
            <div class="menu-actions">
                <select id="is_consumable">
                    <option value="">Select</option>
                    <option value="1">Consumables</option>
                    <option value="0">Non-Consumables</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search ingredients...">
                <button class="add-btn" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                    + Add New Ingredient
                </button>
            </div>
        </div>

        <table class="menu-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>INGREDIENT</th>
                    <th>STOCK</th>
                    <th>UNIT</th>
                    <th>Type</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ingredients)): ?>
                    <?php foreach ($ingredients as $ingredient): ?>
                        <tr>
                            <td><?= htmlspecialchars($ingredient['id']); ?></td>
                            <td><?= htmlspecialchars($ingredient['name']); ?></td>
                            <td><?= htmlspecialchars($ingredient['stock']); ?></td>
                            <td><?= htmlspecialchars($ingredient['unit']); ?></td>
                            <td>
                                <span class="tag">
                                    <?= $ingredient['is_consumable'] ? 'Consumable' : 'Non-Consumable'; ?>
                                </span>
                            <td>
                                <span class="status-tag 
                                    <?= $ingredient['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>">
                                    <?= ucfirst($ingredient['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="edit edit-btn" data-bs-toggle="modal"
                                    data-bs-target="#updateIngredientModal<?= $ingredient['id']; ?>">

                                    Edit
                                </a>
                                <!-- Update Ingredient Modal -->
                                <div class="modal fade" id="updateIngredientModal<?= $ingredient['id']; ?>" tabindex="-1"
                                    aria-labelledby="updateIngredientModalLabel<?= $ingredient['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Ingredient</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form action="" method="POST">
                                                    <input type="hidden" name="update_id" value="<?= $ingredient['id']; ?>">

                                                    <label>Ingredient Name</label>
                                                    <input type="text" name="name" class="form-control mb-2" required
                                                        value="<?= htmlspecialchars($ingredient['name']); ?>">

                                                    <label>Stock</label>
                                                    <input type="number" name="stock" class="form-control mb-2" required
                                                        value="<?= htmlspecialchars($ingredient['stock']); ?>">

                                                    <label>Unit</label>
                                                    <select name="unit" class="form-control mb-2" required>
                                                        <option value="grams" <?= $ingredient['unit'] === 'grams' ? 'selected' : ''; ?>>Grams</option>
                                                        <option value="milliliters" <?= $ingredient['unit'] === 'milliliters' ? 'selected' : ''; ?>>Milliliters</option>
                                                        <option value="pieces" <?= $ingredient['unit'] === 'pieces' ? 'selected' : ''; ?>>Pieces</option>
                                                    </select>

                                                    <label>Status</label>
                                                    <select name="status" class="form-control mb-2" required>
                                                        <option value="active" <?= $ingredient['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                        <option value="inactive" <?= $ingredient['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                    </select>

                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="is_consumable"
                                                            id="is_consumable_<?= $ingredient['id']; ?>" value="1"
                                                            <?= $ingredient['is_consumable'] ? 'checked' : ''; ?>>
                                                        <label class="form-check-label"
                                                            for="is_consumable_<?= $ingredient['id']; ?>">
                                                            Consumable
                                                        </label>
                                                    </div>

                                                    <div class="modal-buttons">
                                                        <button type="submit" class="save-btn"
                                                            name="update_ingredient">Save</button>
                                                        <button type="button" class="cancel-btn"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <a href="admin_delivery.php?delete_ingredient=<?= $ingredient['id']; ?>" class="delete"
                                    onclick="return confirm('Delete this ingredient?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No ingredients added yet!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Ingredient Modal -->
    <div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <label>Ingredient Name</label>
                        <input type="text" name="name" class="form-control mb-2" required
                            placeholder="Enter ingredient name">

                        <label>Stock</label>
                        <input type="number" name="stock" class="form-control mb-2" required
                            placeholder="Enter stock quantity">

                        <label>Unit</label>
                        <select name="unit" class="form-control mb-2" required>
                            <option value="grams">Grams</option>
                            <option value="milliliters">Milliliters</option>
                            <option value="pieces">pieces</option>

                        </select>

                        <label>Status</label>
                        <select name="status" class="form-control mb-2" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_consumable" id="is_consumable"
                                value="1">
                            <label class="form-check-label" for="is_consumable">
                                Consumable
                            </label>
                        </div>

                        <div class="modal-buttons">
                            <button type="submit" class="save-btn" name="add_ingredient">Save</button>
                            <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Search functionality
            document.getElementById("searchInput").addEventListener("input", function () {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll(".menu-table tbody tr");

                rows.forEach(row => {
                    const ingredientName = row.cells[1].textContent.toLowerCase();

                    if (ingredientName.includes(searchTerm)) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });

            // Filter by consumable
            document.getElementById("is_consumable").addEventListener("change", function () {
                const filterValue = this.value;
                const rows = document.querySelectorAll(".menu-table tbody tr");

                rows.forEach(row => {

                    const isConsumable = row.cells[4].textContent.trim() === (filterValue === "1" ? "Consumable" : "Non-Consumable");

                    if (filterValue === "" || isConsumable) {
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
```