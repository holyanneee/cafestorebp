<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Sidebar</title>
   <link rel="stylesheet" href="css/admin_sidebar.css">
   <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<aside class="sidebar">
   <nav class="navbar">
      <a href="cashier_page.php"><i class="fas fa-home"></i> Home</a>

      <!-- Dashboard -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-chart-line"></i> Dashboard</a>
         <div class="dropdown-content">
            <a href="admin_overview.php"><i class="fas fa-tachometer-alt"></i> Overview</a>
            <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Data Analytics</a>
         </div>
      </div>

      <!-- Order Management -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-shopping-cart"></i> Orders</a>
         <div class="dropdown-content">
            <a href="cashier_orders.php"><i class="fas fa-boxes"></i> All Orders</a>
            <a href="cashier_online_orders.php"><i class="fas fa-truck"></i> Online Orders</a>
            <a href="cashier_take_order.php"><i class="fas fa-store"></i> Takeaway & Dine-in Orders</a>
         </div>
      </div>
   </nav>
</aside>

</body>
</html>
