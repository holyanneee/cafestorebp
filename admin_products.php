<?php
@include 'config.php';
session_start();

require_once 'enums/OrderStatusEnum.php';
use enums\OrderStatusEnum;

$admin_id = $_SESSION['admin_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Update delivery status
if (isset($_POST['update_delivery'])) {
    $delivery_id = $_POST['delivery_id'];
    $update_status = filter_var($_POST['update_status'], FILTER_SANITIZE_STRING);
    
    $update_delivery = $conn->prepare("UPDATE `deliveries` SET status = ? WHERE id = ?");
    $update_delivery->execute([$update_status, $delivery_id]);
    
    header('location:admin_deliveries.php');
    exit();
}

// Fetch all deliveries with order information
$select_deliveries = $conn->prepare("
    SELECT d.*, o.name as customer_name, o.email, o.placed_on, 
           (SELECT SUM(op2.subtotal) FROM `order_products` op2 WHERE op2.order_id = o.id) AS total_price
    FROM `deliveries` d
    JOIN `orders` o ON d.order_id = o.id
");
$select_deliveries->execute();
$deliveries = $select_deliveries->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Deliveries</title>
    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/admin_style.css">
    <style>
        :root {
            --table-margin-top: 80px;
        }

        .delivery-container {
            padding: 20px;
            background: white;
            border-radius: 8px;
            margin: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            font-family: 'Segoe UI', sans-serif;
            margin-left: 220px;
            margin-top: var(--table-margin-top);
        }

        .delivery-header {
            display: block;
            margin-bottom: 20px;
        }

        .delivery-header h2 {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 15px;
            text-align: left;
            width: 100%;
        }

        .delivery-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            justify-content: flex-end;
            width: 100%;
        }

        .delivery-actions select,
        .delivery-actions input {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Roboto', sans-serif;
            font-size: 16px;
        }

        .delivery-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
        }

        .delivery-table thead {
            background-color: #f5f5f5;
            text-align: left;
        }

        .delivery-table th {
            font-weight: bold;
        }

        .delivery-table th,
        .delivery-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .status-tag {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff7e6;
            color: #e67e22;
        }

        .status-processing {
            background-color: #e6f2ff;
            color: #2c7be5;
        }

        .status-shipped {
            background-color: #e6f7ff;
            color: #00aaff;
        }

        .status-otw {
            background-color: #e6ffe6;
            color: #00cc66;
        }

        .status-delivered {
            background-color: #e6f7e6;
            color: #27ae60;
        }

        .status-cancelled {
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

        .tracking-progress {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 10px 0;
        }

        .tracking-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            transform: translateY(-50%);
            z-index: 1;
        }

        .tracking-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .step-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 5px;
            color: white;
            font-size: 12px;
        }

        .step-label {
            font-size: 11px;
            color: #777;
            text-align: center;
        }

        .step-active .step-icon {
            background: #4a6fdc;
        }

        .step-completed .step-icon {
            background: #27ae60;
        }

        @media (max-width: 768px) {
            .delivery-container {
                margin-left: 0;
                margin: 15px;
            }
            
            .delivery-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .delivery-actions {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .delivery-actions select,
            .delivery-actions input {
                flex-grow: 1;
                min-width: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>

    <div class="delivery-container">
        <div class="delivery-header">
            <h2>Manage Deliveries</h2>
            <div class="delivery-actions">
                <select id="statusFilter">
                    <option value="">All Statuses</option>

                </select>
                <input type="text" id="searchInput" placeholder="Search deliveries...">
            </div>
        </div>

        <table class="delivery-table">
            <thead>
                <tr>
                    <th>ORDER ID</th>
                    <th>CUSTOMER</th>
                    <th>ORDER DATE</th>
                    <th>TRACKING</th>
                    <th>STATUS</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($deliveries)): ?>
                    <?php foreach ($deliveries as $delivery): ?>
                        <?php 
                        $status_class = 'status-' . $delivery['status'];
                        $order_date = date('M d, Y', strtotime($delivery['placed_on']));
                        ?>
                        <tr data-status="<?= $delivery['status'] ?>">
                            <td>#<?= $delivery['order_id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($delivery['customer_name']) ?></strong><br>
                                <small><?= htmlspecialchars($delivery['email']) ?></small>
                            </td>
                            <td><?= $order_date ?></td>
                            <td>
                                <div class="tracking-progress">
                                    <div class="tracking-step <?= in_array($delivery['status'], ['processing', 'shipped', 'otw', 'delivered']) ? 'step-completed' : ($delivery['status'] == 'pending' ? 'step-active' : '') ?>">
                                        <div class="step-icon"><i class="fas fa-box"></i></div>
                                        <div class="step-label">Preparing</div>
                                    </div>
                                    <div class="tracking-step <?= in_array($delivery['status'], ['shipped', 'otw', 'delivered']) ? 'step-completed' : ($delivery['status'] == 'processing' ? 'step-active' : '') ?>">
                                        <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                                        <div class="step-label">Shipped</div>
                                    </div>
                                    <div class="tracking-step <?= in_array($delivery['status'], ['otw', 'delivered']) ? 'step-completed' : ($delivery['status'] == 'shipped' ? 'step-active' : '') ?>">
                                        <div class="step-icon"><i class="fas fa-truck"></i></div>
                                        <div class="step-label">On The Way</div>
                                    </div>
                                    <div class="tracking-step <?= $delivery['status'] == 'delivered' ? 'step-completed' : ($delivery['status'] == 'otw' ? 'step-active' : '') ?>">
                                        <div class="step-icon"><i class="fas fa-check"></i></div>
                                        <div class="step-label">Delivered</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-tag <?= $status_class ?>">
                                    <?= ucfirst($delivery['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="edit edit-btn" 
                                    data-id="<?= $delivery['id'] ?>"
                                    data-status="<?= $delivery['status'] ?>"
                                    data-bs-toggle="modal" data-bs-target="#updateDeliveryModal">
                                    Update
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No deliveries found!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Update Delivery Modal -->
    <div class="modal fade" id="updateDeliveryModal" tabindex="-1" aria-labelledby="updateDeliveryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Delivery Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="" method="POST">
                        <input type="hidden" name="delivery_id" id="delivery_id">
                        
                        <label>Status</label>
                        <select name="update_status" id="update_status" class="form-control mb-3" required>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="otw">On The Way</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>

                        <div class="modal-buttons">
                            <button type="submit" class="save-btn" name="update_delivery">Update</button>
                            <button type="button" class="cancel-btn" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Populate update modal with delivery data
            document.querySelectorAll(".edit-btn").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("delivery_id").value = this.dataset.id;
                    document.getElementById("update_status").value = this.dataset.status;
                });
            });

            // Status filter functionality
            document.getElementById("statusFilter").addEventListener("change", function() {
                const selectedStatus = this.value;
                const rows = document.querySelectorAll(".delivery-table tbody tr");
                
                rows.forEach(row => {
                    if (selectedStatus === "" || row.dataset.status === selectedStatus) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                    }
                });
            });

            // Search functionality
            document.getElementById("searchInput").addEventListener("input", function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll(".delivery-table tbody tr");
                
                rows.forEach(row => {
                    const customerName = row.querySelector("td:nth-child(2) strong").textContent.toLowerCase();
                    const customerEmail = row.querySelector("td:nth-child(2) small").textContent.toLowerCase();
                    const orderId = row.querySelector("td:nth-child(1)").textContent.toLowerCase();
                    
                    if (customerName.includes(searchTerm) || customerEmail.includes(searchTerm) || orderId.includes(searchTerm)) {
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