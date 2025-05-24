<?php
@include 'config.php';
session_start();

$admin_id = $_SESSION['admin_id'] ?? null;

if (!$admin_id) {
    header('location:login.php');
    exit();
}

// get all earnings for current year for each month
$current_year = date('Y');
$earnings = [];
$monthly_orders = [];
$topFiveProducts = [];
$soldProductsPerMonth = [];
$soldProductsPerMonth = [];

for ($month = 1; $month <= 12; $month++) {
    $start_date = "$current_year-$month-01";
    $end_date = date("Y-m-t", strtotime($start_date));

    // Earnings for the month
    $earnings_query = "SELECT SUM(op.price * op.quantity) AS total_earnings FROM order_products op JOIN orders o ON op.order_id = o.id WHERE o.placed_on BETWEEN :start_date AND :end_date AND YEAR(o.placed_on) = :current_year";
    $stmt = $conn->prepare($earnings_query);
    $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date, ':current_year' => $current_year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $earnings[] = (float) ($row['total_earnings'] ?? 0);

    // Orders count for the month
    $orders_query = "SELECT COUNT(*) AS total_orders FROM orders WHERE placed_on BETWEEN :start_date AND :end_date AND YEAR(placed_on) = :current_year";
    $stmt = $conn->prepare($orders_query);
    $stmt->execute([':start_date' => $start_date, ':end_date' => $end_date, ':current_year' => $current_year]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $monthly_orders[] = (int) ($row['total_orders'] ?? 0);
    $soldProductsPerMonth[] = (int) ($row['total_orders'] ?? 0);

}

// Top 5 products by earnings and sold quantities
$topFiveProductsQuery = "SELECT op.product_id, p.name, SUM(op.price * op.quantity) AS earnings, SUM(op.quantity) AS sold_quantity FROM order_products op JOIN products p ON op.product_id = p.id GROUP BY op.product_id ORDER BY earnings DESC LIMIT 5";
$stmt = $conn->prepare($topFiveProductsQuery);
$stmt->execute();
$topFiveProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$earningsByType = [];

// Calculate earnings by product type
$productTypesQuery = "SELECT p.type, SUM(op.price * op.quantity) AS total_earnings FROM order_products op JOIN products p ON op.product_id = p.id GROUP BY p.type";
$stmt = $conn->prepare($productTypesQuery);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $earningsByType[$row['type']] = (float) $row['total_earnings'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analytics</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

    <div class="container admin-container">
        <h2>Data Analytics</h2>

        <!-- Summary Cards -->
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card p-3">
                    <h5>Total Earnings (<?= $current_year ?>)</h5>
                    <h3>₱<?php echo number_format(array_sum($earnings), 2); ?></h3>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3">
                    <h5>Total Orders (<?= $current_year ?>)</h5>
                    <h3><?php echo array_sum($monthly_orders); ?></h3>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <!-- Monthly Earnings and Orders Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card p-3" style="height: 400px;">
                    <canvas id="totalEarningsPerMonthCanvas"></canvas>
                </div>
            </div>

            <!-- Top Products Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="card p-3" style="height: 400px;">
                    <canvas id="topFiveProductsEarningsCanvas" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <!-- Monthly Earnings and Orders Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="card p-3" style="height: 400px;">
                    <canvas id="soldProductsPerMonthCanvas"></canvas>
                </div>
            </div>

            <!-- Earnings by Product Type -->
             <div class="col-xl-4 col lg-5">
                 <div class="card p-3" style="height: 400px;">
                     <canvas id="earningsByProductTypeCanvas" width="400" height="400"></canvas>
                 </div>
             </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Earnings
        const totalEarningsPerMonthChart = document.getElementById('totalEarningsPerMonthCanvas').getContext('2d');
        const totalEarningsPerMonthData = <?php echo json_encode($earnings); ?>;
        new Chart(totalEarningsPerMonthChart, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    { label: 'Monthly Earnings', data: totalEarningsPerMonthData, borderColor: '#4a6fdc', fill: false },
                ]
            }
        });


        const topFiveProductsEarningsChart = document.getElementById('topFiveProductsEarningsCanvas').getContext('2d');
        const topFiveProductsEarningsData = <?php echo json_encode(array_column($topFiveProducts, 'earnings')); ?>;
        const topFiveProductsLabels = <?php echo json_encode(array_column($topFiveProducts, 'name')); ?>;
        new Chart(topFiveProductsEarningsChart, {
            type: 'bar',
            data: {
                labels: topFiveProductsLabels,
                datasets: [{ label: 'Earnings', data: topFiveProductsEarningsData, backgroundColor: '#27ae60' }]
            },
            options: {
                indexAxis: 'y'
            }
        });

        const soldProductsPerMonthChart = document.getElementById('soldProductsPerMonthCanvas').getContext('2d');
        const soldProductsPerMonthData = <?php echo json_encode($soldProductsPerMonth); ?>;
        new Chart(soldProductsPerMonthChart, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    { label: 'Monthly Products Sold', data: soldProductsPerMonthData, borderColor: '#4a6fdc', fill: false },
                ]
            }
        });

        const earningsByTypePieChart = document.getElementById('earningsByProductTypeCanvas').getContext('2d');
        const earningsByTypeData = <?php echo json_encode(array_values($earningsByType)); ?>;
        const earningsByTypeLabels = <?php echo json_encode(array_keys($earningsByType)); ?>;
        new Chart(earningsByTypePieChart, {
            type: 'pie',
            data: {
                labels: earningsByTypeLabels,
                datasets: [{
                    label: 'Earnings by Product Type',
                    data: earningsByTypeData,
                    backgroundColor: ['#4a6fdc', '#27ae60', '#e67e22', '#d32f2f', '#9b59b6']
                }]
            }
        });
    </script>

</body>

</html>