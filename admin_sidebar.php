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
      <a href="admin_page.php"><i class="fas fa-home"></i> Home</a>

      <!-- Dashboard -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-chart-line"></i> Dashboard</a>
         <div class="dropdown-content">
            <a href="admin_overview.php"><i class="fas fa-tachometer-alt"></i> Overview</a>
            <a href="admin_analytics.php"><i class="fas fa-chart-pie"></i> Data Analytics</a>
         </div>
      </div>

      <!-- Product & Menu Management -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-box"></i> Product & Menu</a>
         <div class="dropdown-content">
            <a href="admin_inventory.php"><i class="fas fa-shopping-basket"></i> Online Store Inventory</a>
            <a href="admin_delivery.php"><i class="fas fa-utensils"></i> Café Menu Management</a>
            <a href="admin_ingredients.php"><i class="fas fa-utensils"></i> Ingredients Inventory</a>
         </div>
      </div>

      <!-- Order Management -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-shopping-cart"></i> Orders</a>
         <div class="dropdown-content">
            <a href="admin_orders.php"><i class="fas fa-boxes"></i> All Orders</a>
            <a href="admin_products.php"><i class="fas fa-truck"></i> Delivery Management</a>
            <a href="admin_takeaway.php"><i class="fas fa-store"></i> Takeaway & Dine-in Orders</a>
         </div>
      </div>

      <!-- User Management -->
      <div class="dropdown menu-section">
         <a href="#" class="dropdown-btn"><i class="fas fa-users"></i> Users</a>
         <div class="dropdown-content">
            <a href="admin_users.php"><i class="fas fa-user-cog"></i> Manage Admins & Staff</a>
            <a href="admin_roles.php"><i class="fas fa-user-shield"></i> Assign Roles & Permissions</a>
         </div>
      </div>

      <!-- Other Pages -->
    
      <a href="admin_contacts.php" class="menu-section"><i class="fas fa-envelope"></i> Messages</a>
   </nav>
</aside>

</body>
</html>
