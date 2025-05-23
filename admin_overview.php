<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit();
}
//default 
// fetch earnings for the current month
$month = date('m');
$year = date('Y');
$select_monthly_earnings = $conn->prepare("SELECT SUM(op.price * op.quantity) AS total_monthly_earnings FROM order_products op JOIN orders o ON op.order_id = o.id WHERE MONTH(o.placed_on) = ? AND YEAR(o.placed_on) = ? AND o.payment_status = 'completed'");
$select_monthly_earnings->execute([$month, $year]);
$fetch_monthly_earnings = $select_monthly_earnings->fetch(PDO::FETCH_ASSOC);
$total_monthly_earnings = $fetch_monthly_earnings['total_monthly_earnings'] ?? 0;

// fetch earnings for the current year
$select_annual_earnings = $conn->prepare("SELECT SUM(op.price * op.quantity) AS total_annual_earnings FROM order_products op JOIN orders o ON op.order_id = o.id WHERE YEAR(o.placed_on) = ? AND o.payment_status = 'completed'");
$select_annual_earnings->execute([$year]);
$fetch_annual_earnings = $select_annual_earnings->fetch(PDO::FETCH_ASSOC);
$total_annual_earnings = $fetch_annual_earnings['total_annual_earnings'] ?? 0;

// fetch total orders current month
$select_monthly_orders = $conn->prepare("SELECT COUNT(*) AS total_monthly_orders FROM orders WHERE MONTH(placed_on) = ? AND YEAR(placed_on) = ? AND payment_status = 'completed'");
$select_monthly_orders->execute([$month, $year]);
$fetch_monthly_orders = $select_monthly_orders->fetch(PDO::FETCH_ASSOC);
$total_monthly_orders = $fetch_monthly_orders['total_monthly_orders'] ?? 0;

// fetch total orders current year
$select_annual_orders = $conn->prepare("SELECT COUNT(*) AS total_annual_orders FROM orders WHERE YEAR(placed_on) = ? AND payment_status = 'completed'");
$select_annual_orders->execute([$year]);
$fetch_annual_orders = $select_annual_orders->fetch(PDO::FETCH_ASSOC);
$total_annual_orders = $fetch_annual_orders['total_annual_orders'] ?? 0;


// fetch popular orders
$select_popular_products = $conn->prepare("
    SELECT 
        op.product_id, 
        p.name AS product_name, 
        p.image AS product_image,
        SUM(op.quantity) AS total_quantity,
        SUM(op.price * op.quantity) AS subtotal
    FROM order_products op 
    JOIN products p ON op.product_id = p.id 
    JOIN orders o ON op.order_id = o.id
    WHERE o.payment_status = 'completed'
    GROUP BY op.product_id 
    ORDER BY total_quantity DESC 
    LIMIT 5
");

$select_popular_products->execute();
$popular_products = $select_popular_products->fetchAll(PDO::FETCH_ASSOC);

$select_recent_order = $conn->prepare("
    SELECT 
        o.id AS order_id, 
        o.name, 
        SUM(op.price * op.quantity) AS total_amount, 
        o.payment_status, 
        o.placed_on
    FROM orders o 
    JOIN order_products op ON o.id = op.order_id 
    GROUP BY o.id 
    ORDER BY o.placed_on DESC 
    LIMIT 5
");

$select_recent_order->execute();
$recent_orders = $select_recent_order->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Overview</title>
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
                <div class="d-sm-flex align-items-center justify-content-between mb-5">
                    <h1 class="h2 mb-0 text-gray-800">Overview</h1>
                </div>

                <!-- Content Row -->
                <div class="row mb-5">

                    <!-- Earnings (Monthly) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100  py-5">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center h-100 px-3">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Earnings (Monthly)</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800">
                                            ₱ <?= number_format($total_monthly_earnings, 2) ?>
                                        </div>

                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Earnings (Monthly) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100  py-5">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center h-100 px-3">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Earnings (Annual)</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800">
                                            ₱ <?= number_format($total_annual_earnings, 2) ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Orders (Monthly) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-info shadow h-100  py-5">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center h-100 px-3">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Orders
                                            (Monthly)</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= $total_monthly_orders ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Orders (Annual) -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100  py-5">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center h-100 px-3">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total
                                            Orders
                                            (Annual)</div>
                                        <div class="h2 mb-0 font-weight-bold text-gray-800"><?= $total_annual_orders ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Content Row -->

                <div class="row">

                    <!-- Recent Orders -->

                    <div class="col-xl-8 col-lg-7">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Order ID</th>
                                                <th>Customer Name</th>
                                                <th>Total Amount</th>
                                                <th>Status</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($recent_orders)): ?>
                                                <?php foreach ($recent_orders as $recent_order): ?>
                                                    <tr>
                                                        <td><?= $recent_order['order_id'] ?></td>
                                                        <td><?= $recent_order['name'] ?></td>
                                                        <td>₱ <?= number_format($recent_order['total_amount'], 2) ?></td>
                                                        <td>
                                                            <span
                                                                class="status-badge status-<?= strtolower($recent_order['payment_status']) ?>">
                                                                <?= ucfirst($recent_order['payment_status']) ?>
                                                            </span>
                                                        </td>
                                                        <td><?= date('F j, Y', strtotime($recent_order['placed_on'])) ?></td>
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
                        </div>
                    </div>
                    <!-- Popular Products -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card shadow mb-4">
                            <!-- Card Header - Dropdown -->
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Popular Products</h6>
                            </div>
                            <!-- Card Body -->
                            <div class="card-body">
                                <?php foreach ($popular_products as $product): ?>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center ">
                                            <img src="uploaded_img/<?= $product['product_image'] ?>"
                                                alt="<?= $product['product_name'] ?>" class="img-fluid rounded-circle me-3"
                                                style="width: 50px; height: 50px;">
                                            <div>
                                                <h6 class="mb-0"><?= $product['product_name'] ?></h6>
                                                <small>Sold: <?= $product['total_quantity'] ?></small>
                                            </div>

                                        </div>
                                        <div>
                                            <small>Earnings: <?= $product['subtotal'] ?></small>
                                        </div>

                                    </div>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>



                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->


        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


</body>

</html>