<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default store selection if not set
if (!isset($_SESSION['store'])) {
    $_SESSION['store'] = 'kape_milagrosa';
}

// Define store switch mapping
$stores = [
    'kape_milagrosa' => 'anak_ng_birhen',
    'anak_ng_birhen' => 'kape_milagrosa'
];

// Handle store switching
if (isset($_GET['switch_store'])) {
    $_SESSION['store'] = $stores[$_SESSION['store']];
    header("Location: " . ($_SESSION['store'] === 'kape_milagrosa' ? "home.php" : "home2.php"));
    exit();
}

// Store display names and logos
$store_data = [
    'kape_milagrosa' => [
        'name' => 'Kape Milagrosa',
        'logo' => 'images/kape_milag.jpg' // Update with the actual path
    ],
    'anak_ng_birhen' => [
        'name' => 'Anak ng Birhen',
        'logo' => 'images/anak_milag.png' // Update with the actual path
    ]
];

$current_store = $_SESSION['store'];
?>

<header class="header">
   <div class="flex">

      <!-- Store switch button (Single Button) -->
      <div class="store-switch">
         <a href="?switch_store=1" class="btn"> <?= $store_data[$stores[$current_store]]['name']; ?></a>
      </div>

      <!-- Store Logo + Name -->
      <div class="logo-container">
         <span class="store-name"><?= $store_data[$current_store]['name']; ?></span>
         <img src="<?= $store_data[$current_store]['logo']; ?>" alt="Store Logo" class="store-logo">
      </div>

      <nav class="navbar">
         <a href="home.php">home</a>
         <a href="shop.php">shop</a>
         <a href="orders.php">orders</a>
         <a href="about.php">about</a>
         <a href="contact.php">contact</a>
      </nav>

      <div class="icons">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user"></div>
         <a href="search_page.php" class="fas fa-search"></a>
         <?php
            $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $count_cart_items->execute([$user_id]);
            $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ?");
            $count_wishlist_items->execute([$user_id]);
         ?>
         <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $count_wishlist_items->rowCount(); ?>)</span></a>
         <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $count_cart_items->rowCount(); ?>)</span></a>
      </div>
      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$user_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="user_profile_update.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
         <div class="flex-btn">
        
         </div>
      </div>

   </div>
</header>

<!-- CSS Styling -->
<style>
   .logo-container {
      display: flex;
      align-items: center;
      text-decoration: none;
   }

   .store-name {
      font-size: 1.5rem;
      font-weight: bold;
      margin-right: 10px;
      color: #333; /* Adjust color */
   }

   .store-logo {
      width: 40px; /* Adjust size as needed */
      height: 40px;
      border-radius: 50%; /* Makes it circular */
   }
</style>
