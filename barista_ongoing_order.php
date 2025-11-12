<?php
@include 'config.php';
session_start();
require_once 'enums/OrderStatusEnum.php';
require_once 'helpers/FormatHelper.php';

use Helpers\FormatHelper;
use Enums\OrderStatusEnum;

$preparingStatus = OrderStatusEnum::Preparing;
$onTheWayStatus = OrderStatusEnum::OnTheWay;
$pickUpStatus = OrderStatusEnum::PickUp;
$completedStatus = OrderStatusEnum::Completed;

$admin_id = $_SESSION['barista_id'] ?? null;
$barista = $_SESSION['barista_name'] ?? 'Unknown'; // Fetch the cashier's name
if (!$admin_id) {
    header('location:login.php');
    exit();
}

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    $status = OrderStatusEnum::OnTheWay->value;
    // get the order 
    $select_order = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
    $select_order->execute([$order_id]);
    $order = $select_order->fetch(PDO::FETCH_ASSOC);
    if (!$order) {
        // Order not found, redirect back with an error message
        header('location:barista_ongoing_order.php');
        exit();
    }


    


    // Redirect to the same page to avoid resubmission
    header('location:barista_orders.php');
    exit();
}

if (isset($_POST['update_order']) && isset($_POST['is_ajax'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $update_payment = filter_var($_POST['update_payment'], FILTER_SANITIZE_SPECIAL_CHARS);

    if (!$order_id || !$update_payment) {
        error_log("Invalid input for order update.");
        http_response_code(400); // Bad Request
        exit();
    }

    // Fetch current status
    $stmt = $conn->prepare("SELECT status FROM `orders` WHERE id = ?");
    $stmt->execute([$order_id]);
    $current_order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_order && $current_order['status'] === $preparingStatus->value && $update_payment === 'completed') {
  
        $update_orders = $conn->prepare("UPDATE `orders` SET status = ?, updated_by_barista = ? WHERE id = ?");
    $update_orders->execute([$completedStatus->value, $barista, $order_id]);

    // deduct the stock of products in order_products
    $select_order_products = $conn->prepare("SELECT * FROM `order_products` WHERE order_id = ?");
    $select_order_products->execute([$order_id]);
    $order_products = $select_order_products->fetchAll(PDO::FETCH_ASSOC);

    foreach ($order_products as $order_product) {
        $product_id = $order_product['product_id'];
        $quantity = $order_product['quantity'];
        $ingredients = json_decode($order_product['ingredients'], true) ?: [];
        $add_ons = json_decode($order_product['add_ons'], true) ?: [];

        // Update the stock on the ingredients table
        foreach ($ingredients as $ingredient_id => $ingredient) {
            // Deduct the quantity from the stock for each ingredient
            $update_ingredient = $conn->prepare("UPDATE `ingredients` SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
            $update_ingredient->execute([$quantity, $ingredient_id]);
        }

        $cup_size_data = json_decode($order_product['cup_sizes'], true);

        if (!empty($cup_size_data) && is_array($cup_size_data)) {
            $cup_size_name = 'cup ' . strtolower($cup_size_data['size']);

            // Fetch the ingredient matching the cup size name (e.g., "Cup Large")
            $select_ingredient = $conn->prepare("SELECT * FROM `ingredients` WHERE LOWER(name) = ? AND is_consumable = 0");
            $select_ingredient->execute([strtolower($cup_size_name)]);
            $ingredient = $select_ingredient->fetch(PDO::FETCH_ASSOC);

            if ($ingredient) {
                // Deduct the quantity from stock for the matching ingredient
                $update_ingredient = $conn->prepare("UPDATE `ingredients` SET stock = GREATEST(stock - ?, 0) WHERE id = ?");
                $update_ingredient->execute([$quantity, $ingredient['id']]);
            }
        }

    }
        header('location:barista_ongoing_order.php');
        exit();
    }

    try {
        $update_orders = $conn->prepare("UPDATE `orders` SET status = ? WHERE id = ?");
        $update_orders->execute([$update_payment, $order_id]);
        http_response_code(200); // OK

        header('location:barista_ongoing_order.php');
        exit();

    } catch (PDOException $e) {
        error_log("Error updating order status: " . $e->getMessage());
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to update order.']);
        exit();
    }
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
        o.address,
        o.type,
        o.receipt,
        o.method,
        o.number,
        o.delivery_fee,
        o.is_walk_in,

        GROUP_CONCAT(op.product_id) AS product_ids,
        (SELECT SUM(op2.subtotal) FROM `order_products` op2 WHERE op2.order_id = o.id) AS total_price
    FROM `orders` o 
    LEFT JOIN `order_products` op ON o.id = op.order_id
    WHERE o.status IN (?, ?, ?) AND o.type = 'coffee'
    GROUP BY o.id
");

$select_orders->execute([$preparingStatus->value, $onTheWayStatus->value, $pickUpStatus->value]);
$orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);


$orders = FormatHelper::formatOrders($orders, $conn);



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> On Going Orders</title>
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
            <h2 class="mb-0" style="font-size: 18px;">On Going Orders</h2>
        </div>

        <!-- <div class="filter-section">
            <div class="filter-container">
                <label class="me-2 fw-bold" style="font-size: 12px;">Order Filters</label>
                <select class="form-select form-select-sm" style="width: 140px;" id="statusFilter">
                    <option value="all" selected>All Statuses</option>
                    <option value="completed">Completed</option>
                    <option value="pending">Pending</option>
                </select>
                <input type="date" class="form-control form-control-sm" style="width: 140px;" id="dateFilter">
                <button class="btn btn-sm btn-outline-secondary" style="font-size: 12px;" id="clearFilters">✖
                    Clear</button>
            </div>
        </div> -->

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
                            <th style="width: 12%">TYPE</th>
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
                                <tr data-id="<?= $order['order_id'] ?>" data-status="<?= $order['status']['value'] ?>"
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
                                         <span class="status-badge" style="background-color: <?= $order['status']['color'] ?>; color: white;">
                                            <?= $order['status']['label'] ?>
                                        </span>
                                    </td>
                                    <td><?= ($order['is_walk_in'] ? 'Walk In' : 'Online') ?></td>
                                    <td>
                                        <button class="action-btn btn-view" data-bs-toggle="modal"
                                            data-bs-target="#viewOrderModal-<?= $order['order_id'] ?>">
                                            View
                                        </button>
                                        <!-- modal -->
                                        <?php include 'view_order_modal.php'; ?>
                                        <button class="action-btn btn-update" data-bs-toggle="modal"
                                            data-bs-target="#updateOrderModal-<?= $order['order_id'] ?>">
                                            Update status
                                        </button>
                                        <div class="modal fade" id="updateOrderModal-<?= $order['order_id'] ?>" tabindex="-1"
                                            aria-labelledby="updateOrderModalLabel-<?= $order['order_id'] ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"
                                                            id="updateOrderModalLabel-<?= $order['order_id'] ?>">Update Order
                                                            Status
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white"
                                                            data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form method="POST" id="updateOrderForm-<?= $order['order_id'] ?>">
                                                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                                        <input type="hidden" name="is_ajax" value="1">
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="updatePaymentStatus-<?= $order['order_id'] ?>"
                                                                    class="form-label">Payment
                                                                    Status</label>
                                                                <select class="form-select form-select-sm" name="update_payment"
                                                                    id="updatePaymentStatus-<?= $order['order_id'] ?>">
                                                                    <?php foreach (OrderStatusEnum::cases() as $status): ?>
                                                                        <?php if (
                                                                            ($order['method'] === 'cash on delivery' && $status->value !== OrderStatusEnum::PickUp->value) ||
                                                                            ($order['method'] === 'pick up' && $status->value !== OrderStatusEnum::OnTheWay->value) ||
                                                                            ($order['method'] !== 'cash on delivery' && $order['method'] !== 'pick up')
                                                                        ): ?>
                                                                            <option value="<?= $status->value ?>"
                                                                                <?= $order['status']['value'] == $status->value ? 'selected' : '' ?>><?= $status->label() ?></option>
                                                                        <?php endif; ?>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm"
                                                                data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="update_order"
                                                                class="btn btn-primary btn-sm">Update Status</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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