<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['barista_id'] ?? null;
$cashier_name = $_SESSION['barista_name'] ?? 'Unknown'; // Fetch the cashier's name
if (!$admin_id) {
    header('location:login.php');
    exit();
}


// Handle AJAX update (silent version)
if (isset($_POST['update_order']) && isset($_POST['is_ajax'])) {
    $order_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];
    $update_payment = filter_var($update_payment, FILTER_SANITIZE_STRING);

    // Update both payment status and cashier in the orders table
    $update_orders = $conn->prepare("UPDATE `orders` SET status = ?, cashier = ? WHERE id = ?");
    $update_orders->execute([$update_payment, $cashier_name, $order_id]);

    http_response_code(204); // No Content
    header('location:cashier_online_order_placed.php');
    exit();
}


// Handle regular form submission
if (isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $update_payment = $_POST['update_payment'];
    $update_payment = filter_var($update_payment, FILTER_SANITIZE_STRING);

    // Update both payment status and cashier in the orders table
    $update_orders = $conn->prepare("UPDATE `orders` SET status = ?, cashier = ? WHERE id = ?");
    $update_orders->execute([$update_payment, $cashier_name, $order_id]);

    header('location:cashier_online_orders.php');
    exit();
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_orders = $conn->prepare("DELETE FROM `orders` WHERE id = ?");
    $delete_orders->execute([$delete_id]);
    header('location:cashier_online_orders.php');
    exit();
}


//  Fetch all orders and its relationship with order_products
$select_orders = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.name,
        o.email,
        o.placed_on,
        o.status,
        o.type,
        GROUP_CONCAT(op.product_id) AS product_ids,
        (SELECT SUM(op2.subtotal) FROM `order_products` op2 WHERE op2.order_id = o.id) AS total_price
    FROM `orders` o 
    LEFT JOIN `order_products` op ON o.id = op.order_id
    WHERE o.status = ?  AND o.barista = ?  AND o.type = 'coffee'
    GROUP BY o.id
");

$select_orders->execute(['completed', $cashier_name]);
$orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

// format the orders
$formatted_orders = [];
foreach ($orders as $order) {
    $formatted_orders[] = [
        'order_id' => $order['order_id'],
        'name' => htmlspecialchars($order['name']),
        'email' => htmlspecialchars($order['email']),
        'placed_on' => date('M d, Y', strtotime($order['placed_on'])),
        'type' => htmlspecialchars($order['type']),
        'status' => htmlspecialchars($order['status']),
        'total_price' => number_format($order['total_price'], 2),
        'products' => array_filter(array_map(function ($product_id) use ($conn, $order) {
            // Fetch product details
            $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
            $select_product->execute([$product_id]);
            $product = $select_product->fetch(PDO::FETCH_ASSOC);
            // Check if product exists
            if (!$product) {
                return null; // Skip if product not found
            }

            // get the quntity and subtotal from order_products
            $select_order_product = $conn->prepare("SELECT * FROM `order_products` WHERE order_id = ? AND product_id = ?");
            $select_order_product->execute([$order['order_id'], $product_id]);
            $order_product = $select_order_product->fetch(PDO::FETCH_ASSOC);
            if (!$order_product) {
                return null; // Skip if order product not found
            }
            $product['quantity'] = $order_product['quantity'];
            $product['subtotal'] = $order_product['subtotal'];

            return [
                'id' => $product['id'],
                'name' => htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                'quantity' => htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'),
                'price' => number_format($product['price'] ?? 0, 2),
                'subtotal' => number_format($product['subtotal'] ?? 0, 2),
                'cup_sizes' => json_decode($order_product['cup_sizes'] ?? '[]', true) ?: [],
                'ingredients' => array_map(function ($ingredient) {
                    return [
                        'name' => htmlspecialchars($ingredient['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'level' => htmlspecialchars($ingredient['level'] ?? '', ENT_QUOTES, 'UTF-8')
                    ];
                }, json_decode($order_product['ingredients'] ?? '[]', true) ?: []),
                'add_ons' => array_map(function ($addOn) {
                    return [
                        'name' => htmlspecialchars($addOn['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'price' => number_format($addOn['price'] ?? 0, 2)
                    ];
                }, json_decode($order_product['add_ons'] ?? '[]', true) ?: []),
            ];
        }, explode(',', $order['product_ids'])))
    ];
}

$orders = $formatted_orders;

// $select_orders = $conn->prepare("SELECT * FROM `orders` 
//     WHERE status = ? 
//     AND barista = ? 
//     AND type = 'coffee'
//     ");

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Completed Orders</title>
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        /* [Previous CSS styles remain exactly the same] */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            font-size: 14px;
        }

        .admin-container {
            margin-left: 220px;
            padding: 20px;
            margin-top: 80px;
        }

        .card {
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border: none;
            margin-top: 50px;
        }

        .table-responsive {
            border-radius: 6px;
            overflow: hidden;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
            table-layout: fixed;
        }

        .table thead th {
            background-color: #4a6fdc;
            color: white;
            font-weight: 600;
            padding: 10px 8px;
            font-size: 12px;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 10px 8px;
            vertical-align: middle;
            font-size: 12px;
            word-wrap: break-word;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .status-completed {
            background-color: #e6f7e6;
            color: #27ae60;
        }

        .status-pending {
            background-color: #fff7e6;
            color: #e67e22;
        }

        .action-btn {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 4px;
            display: inline-block;
            text-align: center;
            min-width: 60px;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-view {
            background-color: #e3f2fd;
            color: #1976d2;
            border-color: #bbdefb;
        }

        .btn-update {
            background-color: #e8f5e9;
            color: #388e3c;
            border-color: #c8e6c9;
        }

        .btn-delete {
            background-color: #ffebee;
            color: #d32f2f;
            border-color: #ffcdd2;
        }

        .filter-section {
            background-color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            display: flex;
            justify-content: flex-end;
        }

        .filter-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pagination-info {
            font-size: 12px;
            color: #6c757d;
            margin-top: 10px;
            text-align: right;
        }

        .modal-content {
            font-size: 13px;
        }

        .modal-header {
            padding: 10px 15px;
            font-size: 14px;
            background-color: #4a6fdc;
            color: white;
        }

        .btn-close-white {
            filter: invert(1);
        }

        .order-details strong {
            width: 120px;
            display: inline-block;
            font-size: 13px;
        }

        .empty-orders {
            padding: 20px;
            font-size: 14px;
            text-align: center;
            color: #666;
        }

        .form-control,
        .form-select {
            font-size: 12px;
            padding: 5px 8px;
            height: 32px;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
    </style>
</head>

<body>
    <?php include 'barista_header.php'; ?>

    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0" style="font-size: 18px;">Completed Orders</h2>
        </div>

        <div class="filter-section">
            <div class="filter-container">
                <label class="me-2 fw-bold" style="font-size: 12px;">Order Filters</label>
                <input type="date" class="form-control form-control-sm" style="width: 140px;" id="dateFilter">
                <button class="btn btn-sm btn-outline-secondary" style="font-size: 12px;" id="clearFilters">✖
                    Clear</button>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 8%">ORDER ID</th>
                            <th style="width: 22%">CUSTOMER</th>
                            <th style="width: 12%">DATE</th>
                            <th style="width: 12%">TOTAL</th>
                            <th style="width: 12%">STATUS</th>
                            <th style="width: 18%">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $order): ?>
                                <?php
                                $status_class = $order['status'] == 'completed' ? 'status-completed' : 'status-pending';
                                $order_date = date('M d, Y', strtotime($order['placed_on']));
                                ?>
                                <tr data-id="<?= $order['order_id'] ?>" data-status="<?= $order['status'] ?>"
                                    data-date="<?= date('Y-m-d', strtotime($order['placed_on'])) ?>">
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td>
                                        <div class="fw-semibold" style="font-size: 12px;">
                                            <?= htmlspecialchars($order['name']) ?>
                                        </div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <?= htmlspecialchars($order['email']) ?>
                                        </div>
                                    </td>
                                    <td><?= $order_date ?></td>
                                    <td>₱<?= number_format((float) str_replace(',', '', $order['total_price']), 2) ?></td>
                                    <td>
                                        <span class="status-badge <?= $status_class ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-view" data-bs-toggle="modal"
                                            data-bs-target="#viewOrderModal-<?= $order['order_id'] ?>">
                                            View
                                        </button>
                                        <!-- modal -->
                                        <?php include 'view_order_modal.php'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="empty-orders">No orders placed yet!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination-info">
            Showing <?= !empty($orders) ? '1-' . count($orders) : '0' ?> of <?= count($orders) ?> orders
        </div>
    </div>



    <!-- Update Order Modal -->
    <div class="modal fade" id="updateOrderModal" tabindex="-1" aria-labelledby="updateOrderModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateOrderModalLabel">Update Order Status</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form method="POST" id="updateOrderForm">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <input type="hidden" name="is_ajax" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="updatePaymentStatus" class="form-label">Payment Status</label>
                            <select class="form-select form-select-sm" name="update_payment" id="updatePaymentStatus">
                                <option value="On Going">Take Order</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_order" class="btn btn-primary btn-sm">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast-container">
        <div id="updateToast" class="toast align-items-center text-white bg-success" role="alert" aria-live="assertive"
            aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Order status updated successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const viewOrderModal = document.getElementById('viewOrderModal');

        // View Order Modal - Load data when shown
        viewOrderModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.getAttribute('data-order-id');

            // Find the order in the table data
            const orderRow = document.querySelector(`tr[data-id="${orderId}"]`);
            if (orderRow) {
                const orderData = {
                    id: orderId,
                    name: orderRow.querySelector('.fw-semibold').textContent,
                    email: orderRow.querySelector('.text-muted').textContent,
                    date: orderRow.cells[2].textContent,
                    total: orderRow.cells[3].textContent,
                    status: orderRow.querySelector('.status-badge').textContent.trim(),
                };

                // Update modal title with order ID
                document.getElementById('modalOrderId').textContent = orderId;

                // Build and display order details
                const detailsHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="order-details mb-2">
                            <strong>Customer Name:</strong> ${orderData.name}
                        </div>
                        <div class="order-details mb-2">
                            <strong>Email:</strong> ${orderData.email}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="order-details mb-2">
                            <strong>Order Date:</strong> ${orderData.date}
                        </div>
                        <div class="order-details mb-2">
                            <strong>Status:</strong> 
                            <span class="status-badge ${orderData.status.toLowerCase() === 'completed' ? 'status-completed' : 'status-pending'}">
                                ${orderData.status}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="order-details mb-3">
                    <strong>Total Amount:</strong> ${orderData.total}
                </div>
                <div class="products-list">
                    <h6 class="fw-bold">Order Information:</h6>
                    <p>Full order details would be displayed here.</p>
                </div>
            `;

                document.getElementById('orderDetailsContent').innerHTML = detailsHTML;
            }
        });

        // Update Order Modal - Set current status when opening
        document.querySelectorAll('.btn-update').forEach(button => {
            button.addEventListener('click', function () {
                const orderId = this.getAttribute('data-id');
                const currentStatus = this.getAttribute('data-status');

                document.getElementById('updateOrderId').value = orderId;
                document.getElementById('updatePaymentStatus').value = currentStatus;
            });
        });
    </script>

</body>

</html>