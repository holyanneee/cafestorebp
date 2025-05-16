<?php
@include 'config.php';
session_start();

// Ensure admin is logged in
$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Delete Product
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    $delete_product = $conn->prepare("DELETE FROM products WHERE id = ? AND type = 'online'");
    $delete_product->execute([$delete_id]);

    header('location:admin_inventory.php');
    exit();
}

// Add Product
if (isset($_POST['add_product'])) {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $details = htmlspecialchars($_POST['details'], ENT_QUOTES, 'UTF-8');
    $status = 'active'; // Default status for new products
    $type = 'online'; // Default type for this page

    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_ext = pathinfo($image, PATHINFO_EXTENSION);
    $unique_image_name = 'prod_' . time() . '.' . $image_ext;
    $image_path = "uploaded_img/{$unique_image_name}";

    if (move_uploaded_file($image_tmp_name, $image_path)) {
        $insert_product = $conn->prepare("INSERT INTO products (name, category, details, price, status, type, stock, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_product->execute([$name, $category, $details, $price, $status, $type, $stock, $unique_image_name]);
    }
}

// Update Product
if (isset($_POST['update_product'])) {
    $update_id = $_POST['update_id'];
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $category = htmlspecialchars($_POST['category'], ENT_QUOTES, 'UTF-8');
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
    $details = htmlspecialchars($_POST['details'], ENT_QUOTES, 'UTF-8');
    $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');

    $image = $_FILES['image']['name'];
    $image_tmp_name = $_FILES['image']['tmp_name'];

    if (!empty($image)) {
        $image_ext = pathinfo($image, PATHINFO_EXTENSION);
        $unique_image_name = 'prod_' . time() . '.' . $image_ext;
        $image_path = 'uploaded_img/' . $unique_image_name;
        move_uploaded_file($image_tmp_name, $image_path);

        // Update query with image
        $update_product = $conn->prepare("UPDATE products SET name=?, category=?, details=?, price=?, status=?, stock=?, image=? WHERE id=? AND type='online'");
        $update_product->execute([$name, $category, $details, $price, $status, $stock, $unique_image_name, $update_id]);
    } else {
        // Update query without image
        $update_product = $conn->prepare("UPDATE products SET name=?, category=?, details=?, price=?, status=?, stock=? WHERE id=? AND type='online'");
        $update_product->execute([$name, $category, $details, $price, $status, $stock, $update_id]);
    }

    header('location:admin_inventory.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Shop Inventory</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="menu-container">
        <div class="menu-header">
            <h2>Manage Inventory</h2>
            <div class="menu-actions">
                <select id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="Chibi Religious Items">Chibi Religious Items</option>
                    <option value="Angels">Angels</option>
                    <option value="Cross">Cross</option>
                    <option value="Prayer Pocket">Prayer Pocket</option>
                    <option value="Rosary">Rosary</option>
                    <option value="Ref Magnet">Ref Magnet</option>
                    <option value="Keychain">Keychain</option>
                    <option value="Scapular">Scapular</option>
                    <option value="Statues">Statues</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search inventory items...">
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
                    <th>STOCK</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $show_products = $conn->prepare("SELECT * FROM products WHERE type = 'online' ORDER BY id DESC");
                $show_products->execute();
                if ($show_products->rowCount() > 0) {
                    while ($fetch_products = $show_products->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr data-category="<?= htmlspecialchars($fetch_products['category']); ?>">
                            <td class="item-col">
                                <img src="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
                                    alt="<?= htmlspecialchars($fetch_products['name']); ?>">
                                <div>
                                    <strong><?= htmlspecialchars($fetch_products['name']); ?></strong>
                                    <p><?= htmlspecialchars($fetch_products['details']); ?></p>
                                </div>
                            </td>
                            <td><span class="tag"><?= htmlspecialchars($fetch_products['category']); ?></span></td>
                            <td>₱<?= htmlspecialchars($fetch_products['price']); ?></td>
                            <td><?= htmlspecialchars($fetch_products['stock']); ?></td>

                            <td>
                                <a href="#" class="edit edit-btn" data-id="<?= $fetch_products['id']; ?>"
                                    data-name="<?= htmlspecialchars($fetch_products['name']); ?>"
                                    data-category="<?= htmlspecialchars($fetch_products['category']); ?>"
                                    data-details="<?= htmlspecialchars($fetch_products['details']); ?>"
                                    data-price="<?= htmlspecialchars($fetch_products['price']); ?>"
                                    data-stock="<?= htmlspecialchars($fetch_products['stock']); ?>"
                                    data-image="uploaded_img/<?= htmlspecialchars($fetch_products['image']); ?>"
                                    data-bs-toggle="modal" data-bs-target="#updateProductModal">
                                    Edit
                                </a>
                                <a href="admin_inventory.php?delete=<?= $fetch_products['id']; ?>" class="delete"
                                    onclick="return confirm('Delete this product?');">
                                    Delete
                                </a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="6" class="text-center">No products added yet!</td></tr>';
                }
                ?>
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
                            <option value="Chibi Religious Items">Chibi Religious Items</option>
                            <option value="Angels">Angels</option>
                            <option value="Cross">Cross</option>
                            <option value="Prayer Pocket">Prayer Pocket</option>
                            <option value="Rosary">Rosary</option>
                            <option value="Ref Magnet">Ref Magnet</option>
                            <option value="Keychain">Keychain</option>
                            <option value="Scapular">Scapular</option>
                            <option value="Statues">Statues</option>
                        </select>

                        <label>Price</label>
                        <input type="number" min="0" name="price" class="form-control mb-2" required
                            placeholder="Enter product price">

                        <label>Stock Quantity</label>
                        <input type="number" min="0" name="stock" class="form-control mb-2" required
                            placeholder="Enter stock quantity">

                        <label>Description</label>
                        <textarea name="details" class="form-control mb-2" required
                            placeholder="Enter product description"></textarea>

                        <label>Image</label>
                        <input type="file" name="image" required class="form-control mb-2"
                            accept="image/jpg, image/jpeg, image/png">

                        <div class="modal-buttons">
                            <button type="submit" class="save-btn" name="add_product">Save</button>
                            <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Product Modal -->
    <div class="modal fade" id="updateProductModal" tabindex="-1" aria-labelledby="updateProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_id" id="update_id">

                        <label>Product Name</label>
                        <input type="text" name="name" id="update_name" class="form-control mb-2" required>

                        <label>Category</label>
                        <select name="category" id="update_category" class="form-control mb-2" required>
                            <option value="Chibi Religious Items">Chibi Religious Items</option>
                            <option value="Angels">Angels</option>
                            <option value="Cross">Cross</option>
                            <option value="Prayer Pocket">Prayer Pocket</option>
                            <option value="Rosary">Rosary</option>
                            <option value="Ref Magnet">Ref Magnet</option>
                            <option value="Keychain">Keychain</option>
                            <option value="Scapular">Scapular</option>
                            <option value="Statues">Statues</option>
                        </select>

                        <label>Price</label>
                        <input type="number" min="0" name="price" id="update_price" class="form-control mb-2" required>

                        <label>Stock Quantity</label>
                        <input type="number" min="0" name="stock" id="update_stock" class="form-control mb-2" required>

                        <label>Description</label>
                        <textarea name="details" id="update_details" class="form-control mb-2" required></textarea>

                        <label>Current Image</label>
                        <img id="update_image" src="" class="img-fluid mb-2" style="max-height: 150px; display: block;">

                        <label>New Image (Leave blank to keep current)</label>
                        <input type="file" name="image" class="form-control mb-2"
                            accept="image/jpg, image/jpeg, image/png">

                        <div class="modal-buttons">
                            <button type="submit" class="save-btn" name="update_product">Update</button>
                            <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --table-margin-top: 80px;
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

        .menu-header {
            display: block;
            margin-bottom: 20px;
        }

        .menu-header h2 {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 15px;
            text-align: left;
            width: 100%;
        }

        .menu-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            width: 100%;
        }

        .menu-actions select,
        .menu-actions input {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
        }

        .add-btn {
            background-color: #ff8c1a;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
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

        .menu-table th {
            font-weight: bold;
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

        .edit {
            color: #e67e22;
            text-decoration: none;
            margin-right: 8px;
        }

        .edit:hover {
            text-decoration: underline;
        }

        .delete {
            color: #e74c3c;
            text-decoration: none;
        }

        .delete:hover {
            text-decoration: underline;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
        }

        .modal-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .modal-content label {
            display: block;
            margin: 10px 0 5px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
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
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
        }

        .save-btn:hover {
            background-color: #219653;
        }

        .cancel-btn {
            background-color: #bdc3c7;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-size: 16px;
        }

        .cancel-btn:hover {
            background-color: #95a5a6;
        }

        @media (max-width: 768px) {
            .menu-container {
                margin-left: 0;
                margin: 15px;
            }
            
            .menu-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .menu-actions {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .menu-actions select,
            .menu-actions input {
                flex-grow: 1;
                min-width: 150px;
            }
        }
        /* Adjust font size in modals */
.modal-content {
    font-size: 14px; /* Adjust as needed */
}

/* Optionally, target specific elements */
.modal-content label,
.modal-content input,
.modal-content select,
.modal-content textarea,
.modal-content button {
    font-size: 14px; /* Keep it consistent */
}

    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Populate update modal with product data
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function () {
                    document.getElementById("update_id").value = this.dataset.id;
                    document.getElementById("update_name").value = this.dataset.name;
                    document.getElementById("update_category").value = this.dataset.category;
                    document.getElementById("update_price").value = this.dataset.price;
                    document.getElementById("update_stock").value = this.dataset.stock;
                    document.getElementById("update_details").value = this.dataset.details;
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

            // Prevent form resubmission
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.href);
            }
        });
    </script>
</body>

</html>