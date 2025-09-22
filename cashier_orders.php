<?php

@include 'config.php';
session_start();

require_once 'enums/OrderStatusEnum.php';
require_once 'helpers/FormatHelper.php';

use Helpers\FormatHelper;
use Enums\OrderStatusEnum;

$preparingStatus = OrderStatusEnum::Preparing;

$admin_id = $_SESSION['cashier_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Handle AJAX update
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
        header('location:cashier_order_error.php');
        exit();
    }

    try {
        $update_orders = $conn->prepare("UPDATE `orders` SET status = ? WHERE id = ?");
        $update_orders->execute([$update_payment, $order_id]);
        http_response_code(200); // OK

        header('location:cashier_orders.php');
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
    header('location:cashier_orders.php');
    exit();
}

$cashier_name = $_SESSION['cashier_name'] ?? null;

if ($cashier_name) {

    //  Fetch all orders and its relationship with order_products
    $select_orders = $conn->prepare("
    SELECT 
        o.id AS order_id,
        MAX(o.name) AS name,
        MAX(o.email) AS email,
        MAX(o.placed_on) AS placed_on,
        MAX(o.status) AS status,
        MAX(o.type) AS type,
        GROUP_CONCAT(op.product_id) AS product_ids,
        (SELECT SUM(op2.subtotal) FROM `order_products` op2 WHERE op2.order_id = o.id) AS total_price
        FROM `orders` o 
        LEFT JOIN `order_products` op ON o.id = op.order_id
        GROUP BY o.id
        ORDER BY o.id 
");
    $select_orders->execute();

    $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);

    // format the orders


    $orders = FormatHelper::formatOrders($orders, $conn);

} else {
    // Optional: handle case where cashier name is not set in session
    $orders = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> All Orders</title>
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
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
    <?php include 'cashier_header.php'; ?>

    <div class="admin-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0" style="font-size: 18px;">All Orders</h2>
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
                        <?php
                        // Sort the orders by id in descending order
                        usort($orders, fn($a, $b) => $b['order_id'] - $a['order_id']);
                        ?>

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
                                    <td>
                                        <?php if ($order['status']['value'] === 'completed'): ?>
                                            <button class="action-btn btn-view" data-bs-toggle="modal"
                                                data-bs-target="#viewOrderModal-<?= $order['order_id'] ?>">
                                                View
                                            </button>

                                            <a href="receipt.php?order_id=<?= $order['order_id'] ?>" class="action-btn btn-view"
                                                style="text-decoration: none;">
                                                Get Receipt
                                            </a>
                                        <?php else: ?>
                                            <button class="action-btn btn-view" data-bs-toggle="modal"
                                                data-bs-target="#viewOrderModal-<?= $order['order_id'] ?>">
                                                View
                                            </button>
                                            <button class="action-btn btn-update" data-bs-toggle="modal"
                                                data-bs-target="#updateOrderModal-<?= $order['order_id'] ?>"
                                                data-id="<?= $order['order_id'] ?>" data-status="<?= $order['status']['value'] ?>">
                                                Update
                                            </button>
                                            <a href="cashier_orders.php?delete=<?= $order['order_id'] ?>"
                                                class="action-btn btn-delete" style="text-decoration: none;"
                                                onclick="return confirm('Delete this order?');">
                                                Delete
                                            </a>
                                        <?php endif; ?>
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
                                                                        <option value="<?= $status->value ?>"
                                                                            <?= $order['status']['value'] == $status->value ? 'selected' : '' ?>><?= $status->label() ?></option>
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


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Update Order Modal -->


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

    <script>
        // // check if the bootstrap JavaScript is working
        // if (typeof bootstrap !== 'undefined') {
        //     console.log('Bootstrap JavaScript is loaded and working.');
        // } else {
        //     console.log('Bootstrap JavaScript is not loaded.');
        // }
        // console.log('JavaScript is running');
        // // For debugging: print orders data to console
        // console.log(<?= json_encode($orders) ?>);
        // Initialize components
        const updateToast = new bootstrap.Toast(document.getElementById('updateToast'));
        const viewOrderModal = document.getElementById('viewOrderModal');

        // View Order Modal - Load data when shown
        viewOrderModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            if (viewOrderModal) {
                viewOrderModal.addEventListener('show.bs.modal', function (event) {

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
                            // Add more fields as needed
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
                                <strong>Status:</strong> <span class="status-badge ${orderData.status.toLowerCase() === 'completed' ? 'status-completed' : 'status-pending'}">
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

        // [Rest of your JavaScript remains the same]
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dateFilter = document.getElementById('dateFilter');
            const clearFilters = document.getElementById('clearFilters');
            const tableRows = document.querySelectorAll('tbody tr');

            function filterOrders() {
                const dateValue = dateFilter.value;

                let hasVisibleRows = false;

                tableRows.forEach(row => {
                    const rowDate = row.getAttribute('data-date');

                    let matchesDate = !dateValue || rowDate === dateValue;

                    if (matchesDate) {
                        row.style.display = '';
                        hasVisibleRows = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const emptyMessageRow = document.querySelector('.empty-orders-row');
                if (!hasVisibleRows) {
                    if (!emptyMessageRow) {
                        const emptyRow = document.createElement('tr');
                        emptyRow.classList.add('empty-orders-row');
                        emptyRow.innerHTML = `<td colspan="6" class="empty-orders">No matching orders found!</td>`;
                        document.querySelector('tbody').appendChild(emptyRow);
                    }
                } else if (emptyMessageRow) {
                    emptyMessageRow.remove();
                }
            }

            dateFilter.addEventListener('change', filterOrders);

            clearFilters.addEventListener('click', function () {
                dateFilter.value = '';
                filterOrders();
            });
        });
    </script>
</body>

</html>