<header class="sidebar">

    <div class="logo">
        <a href="admin_page.php">Admin<span>Panel</span></a>
    </div>

    <nav class="sidebar-nav">
        <a href="admin_page.php"><i class="fas fa-home"></i> Dashboard</a>
        <a href="admin_products.php"><i class="fas fa-box"></i> Products</a>
        <a href="admin_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
        <a href="admin_users.php"><i class="fas fa-users"></i> Users</a>
        <a href="admin_contacts.php"><i class="fas fa-envelope"></i> Messages</a>
        <a href="admin_update_profile.php"><i class="fas fa-user"></i> Profile</a>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>

    <div class="profile-box">
        <?php
        $select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $select_profile->execute([$admin_id]);
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
        ?>
        <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
        <p><?= $fetch_profile['name']; ?></p>
    </div>

</header>
