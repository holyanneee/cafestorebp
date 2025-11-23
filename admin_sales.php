<?php
require 'enums/OrderStatusEnum.php';
use Enums\OrderStatusEnum;

@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit();
}

$completedStatus = OrderStatusEnum::Completed;

// Filters
$type = isset($_GET['type']) && in_array($_GET['type'], ['coffee', 'religious']) ? $_GET['type'] : null;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Query conditions
$typeCondition = $type ? "AND o.type = ?" : "";


$dateCondition = "";
$dateParams = [];
if ($start_date) {
    $dateCondition .= " AND o.placed_on >= ?";
    $dateParams[] = $start_date . ' 00:00:00';
}
if ($end_date) {
    $dateCondition .= " AND o.placed_on <= ?";
    $dateParams[] = $end_date . ' 23:59:59';
}

// Prepare SQL query
$sql = "SELECT 
o.id AS order_id, 
        o.name, 
        SUM(op.price * op.quantity) AS total_amount,
        o.type, 
        o.status, 
        o.placed_on,
        o.is_walk_in,
        SUM(op.quantity) as total_products
    FROM orders o 
    JOIN order_products op ON o.id = op.order_id 
        WHERE o.status = '$completedStatus->value'
         $typeCondition $dateCondition
        GROUP BY o.id, o.name, o.type, o.status, o.placed_on, o.is_walk_in
        ORDER BY o.placed_on DESC";

$stmt = $conn->prepare($sql);
$params = [];
if ($type) {
    $params[] = $type;
}
$params = array_merge($params, $dateParams);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$walkInOrders = array_filter($orders, function ($order) {
    return $order['is_walk_in'] == 1;
});

$onlineOrders = array_filter($orders, function ($order) {
    return $order['is_walk_in'] == 0;
});

// Handle Excel download
if (isset($_GET['donload']) && $_GET['donload'] == '1') {
    require 'services/export.php';

    // Define headers and sheets
    $headers = [
        'Sales-walk-in' => ['Order ID', 'Name', 'Total Amount', 'Total Products', 'Status', 'Date'],
        'Sales-online' => ['Order ID', 'Name', 'Total Amount', 'Total Products', 'Status', 'Date'],
    ];

    $ordersBySheet = [
        'Sales-walk-in' => [],
        'Sales-online' => [],
    ];

    // Populate data for each sheet
    foreach ($orders as $order) {
        $sheetKey = $order['is_walk_in'] == 1 ? 'Sales-walk-in' : 'Sales-online';
        $ordersBySheet[$sheetKey][] = [
            $order['order_id'],
            $order['name'],
            '₱ ' . number_format($order['total_amount'], 2),
            $order['total_products'],
            ucfirst($order['status']),
            date('F j, Y', strtotime($order['placed_on'])),
        ];
    }

    $filename = 'sales_export_' . date('Ymd_His') . '.xlsx';
    $filePath = toExcel($headers, array_keys($headers), $ordersBySheet, $filename);

    // Send file for download
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Sales</title>
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

    <style>
        .text-gray-300 {
            color: #dddfeb !important
        }

        .text-gray-800 {
            color: #5a5c69 !important
        }

        .card {
            position: relative !important;
            display: flex !important;
            flex-direction: column !important;
            min-width: 0 !important;
            word-wrap: break-word !important;
            background-color: #fff !important;
            background-clip: border-box !important;
            border: 1px solid #e3e6f0 !important;
            border-radius: .35rem !important;
            margin-top: 0 !important;
        }

        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -.75rem;
            margin-left: -.75rem;
        }
    </style>
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div id="content-wrapper" class="d-flex flex-column admin-container">

        <!-- Main Content -->
        <div id="content">
            <!-- Begin Page Content -->
            <div class="container-fluid">

                <!-- Page Heading -->
                <div class="row mb-5 align-items-center">
                    <div class="col">
                        <h1 class="h2 mb-0 text-gray-800">Sales</h1>
                    </div>
                    <div class="col">
                        <form method="get" id="filters" class="d-flex align-items-center justify-content-end">
                            <!-- Type Filter -->
                            <div class="">
                                <label for="type" class="me-2">Type:</label>
                                <select name="type" class="form-select" id="type">
                                    <option value="">All Types</option>
                                    <option value="coffee" <?= $type === 'coffee' ? 'selected' : ''; ?>>Coffee</option>
                                    <option value="religious" <?= $type === 'religious' ? 'selected' : ''; ?>>Religious
                                        Items
                                    </option>
                                </select>
                            </div>

                            <!-- Start date Filter -->
                            <div class="ms-3">
                                <label for="start_date" class="me-2">Start Date:</label>
                                <input type="date" name="start_date" class="form-control" id="start_date"
                                    value="<?= htmlspecialchars($start_date) ?>">
                            </div>
                            <!-- End date Filter -->
                            <div class="ms-3">
                                <label for="end_date" class="me-2">End Date:</label>
                                <input type="date" name="end_date" class="form-control" id="end_date"
                                    value="<?= htmlspecialchars($end_date) ?>">
                            </div>
                            <div class="flex-shrink-0 px-2">
                                <a href="admin_sales.php" class="btn btn-sm btn-danger">Reset</a>
                                <a href="admin_sales.php?donload=1" class="btn btn-sm btn-success mt-3">
                                    <i class="fas fa-file-excel"></i>
                                    Download
                                </a>
                            </div>
                        </form>
                    </div>
                </div>



                <!-- Content Row -->

                <div class="row">
                    <!-- Orders -->
                    <div class="col">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h5 class="m-0 font-weight-bold text-primary">Online Orders</h5>

                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Name</th>
                                                <th>Total Amount</th>
                                                <th>Total Products</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($onlineOrders)): ?>
                                                <?php foreach ($onlineOrders as $onlineOrder): ?>
                                                    <tr>
                                                        <td><?= $onlineOrder['order_id'] ?></td>
                                                        <td><?= $onlineOrder['name'] ?></td>
                                                        <td>₱ <?= number_format($onlineOrder['total_amount'], 2) ?></td>
                                                        <td><?= $onlineOrder['total_products'] ?></td>
                                                        <td>
                                                            <span
                                                                class="status-badge status-<?= strtolower($onlineOrder['status']) ?>">
                                                                <?= ucfirst($onlineOrder['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('F j, Y', strtotime($onlineOrder['placed_on'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No recent orders found.</td>
                                                </tr>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>

                                </div>

                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <h5 class="m-0 font-weight-bold text-primary">Total Sales:
                                    ₱ <?= number_format(array_sum(array_column($onlineOrders, 'total_amount')), 2) ?>
                                </h5>
                                <h5 class="m-0 font-weight-bold text-primary">Total Orders:
                                    <?= count($onlineOrders) ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h5 class="m-0 font-weight-bold text-primary">Walk-in Orders</h5>
                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Name</th>
                                                <th>Total Amount</th>
                                                <th>Total Products</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($walkInOrders)): ?>
                                                <?php foreach ($walkInOrders as $walkInOrder): ?>
                                                    <tr>
                                                        <td><?= $walkInOrder['order_id'] ?></td>
                                                        <td><?= $walkInOrder['name'] ?></td>
                                                        <td>₱ <?= number_format($walkInOrder['total_amount'], 2) ?></td>
                                                        <td><?= $walkInOrder['total_products'] ?></td>
                                                        <td>
                                                            <span
                                                                class="status-badge status-<?= strtolower($walkInOrder['status']) ?>">
                                                                <?= ucfirst($walkInOrder['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('F j, Y', strtotime($walkInOrder['placed_on'])) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No recent orders found.</td>
                                                </tr>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <h5 class="m-0 font-weight-bold text-primary">Total Sales:
                                    ₱ <?= number_format(array_sum(array_column($walkInOrders, 'total_amount')), 2) ?>
                                </h5>
                                <h5 class="m-0 font-weight-bold text-primary">Total Orders:
                                    <?= count($walkInOrders) ?>
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->
        </div>


    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // check if the filters are changed
        document.addEventListener('DOMContentLoaded', () => {
            const filterForm = document.getElementById('filters');

            const typeSelect = document.getElementById('type');
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            const initialType = typeSelect.value;
            const initialStartDate = startDate.value;
            const initialEndDate = endDate.value;

            function checkFilters() {
                if (typeSelect.value !== initialType || startDate.value !== initialStartDate || endDate.value !== initialEndDate) {
                    // Submit the form
                    filterForm.submit();
                }
            }

            typeSelect.addEventListener('change', checkFilters);
            startDate.addEventListener('change', checkFilters);
            endDate.addEventListener('change', checkFilters);
        });
    </script>

</body>

</html>