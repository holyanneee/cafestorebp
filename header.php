<?php
ob_start(); // Must be the very first line — starts output buffering

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
   session_start();
}

// Default store selection if not set
if (!isset($_SESSION['store'])) {
   $_SESSION['store'] = 'kape_milagrosa';
   header("Location: index.php");
   exit(); // Always exit after redirect
}


// Store display names and logos
$store_data = [
   'kape_milagrosa' => [
      'name' => 'Kape Milagrosa',
      'logo' => 'images/kape_milag.jpg'
   ],
   'anak_ng_birhen' => [
      'name' => 'Anak ng Birhen',
      'logo' => 'images/anak_milag.png'
   ]
];

$current_store = $_SESSION['store'];

$type = ($current_store === 'kape_milagrosa') ? 'coffee' : 'online';

$user_id = $_SESSION['user_id'] ?? null; // Use null coalescing operator for safety
if (!isset($user_id) && (isset($_GET['cart_product_id']) || isset($_GET['fav_product_id']))) {
   unset($_GET['cart_product_id']);
   unset($_GET['fav_product_id']);
   header('location:login.php');
   exit;
}
if (isset($user_id)) {
   $count_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND type = ?");
   $count_cart_items->execute([$user_id, $type]);
   $count_wishlist_items = $conn->prepare("SELECT * FROM `wishlist` WHERE user_id = ? AND type = ?");
   $count_wishlist_items->execute([$user_id, $type]);
   $count_orders_items = $conn->prepare("SELECT * FROM `orders` WHERE user_id = ? AND type = ?");
   $count_orders_items->execute([$user_id, $type]);

   $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
   $select_profile->execute([$user_id]);
   $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

   if (isset($_GET['fav_product_id'])) {
      $prouct_id = $_GET['fav_product_id'];
      $prouct_id = filter_var($prouct_id, FILTER_SANITIZE_SPECIAL_CHARS);

      $check_favourite_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ? AND type = ?");
      $check_favourite_numbers->execute([$prouct_id, $user_id, $type]);
      if ($check_favourite_numbers->rowCount() > 0) {
         $message[] = 'already added to wishlist!';
      } else {
         $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
         $select_product->execute([$prouct_id]);
         if ($select_product->rowCount() > 0) {
            $fetch_product = $select_product->fetch(PDO::FETCH_ASSOC);
            $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, product_id ,type) VALUES(?,?,?)");
            $insert_wishlist->execute([$user_id, $prouct_id, $type]);
            $message[] = 'added to wishlist!';
         } else {
            $message[] = 'product not found!';
         }
      }
      unset($_GET['fav_product_id']);
   }

   if (isset($_GET['cart_product_id'])) {
      
      $product_id = $_GET['cart_product_id'];

      $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE product_id = ? AND user_id = ?");
      $check_cart_numbers->execute([$product_id, $user_id]);

      if ($check_cart_numbers->rowCount() > 0) {
         // update quantity
      } else {
         $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE product_id = ? AND user_id = ? AND type = ?");
         $check_wishlist_numbers->execute([$product_id, $user_id, $type]);

         if ($check_wishlist_numbers->rowCount() > 0) {
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE product_id = ? AND user_id = ? AND type = ?");
            $delete_wishlist->execute([$product_id, $user_id, $type]);
         }

         $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, product_id, quantity) VALUES(?, ?, 1)");
         $insert_cart->execute([$user_id, $product_id]);
         $message[] = 'added to cart!';
      }
      unset($_GET['cart_product_id']);
   }
}
$category = '';
if (isset($_GET['category'])) {
   $category = $_GET['category'];
}

$select_products = $conn->prepare("SELECT * FROM `products` WHERE `status` = 'active' AND `type` = ? AND `category` != 'Add-ons' ORDER BY id DESC LIMIT 6");
$select_products->execute([$type]);
$products = $select_products->fetchAll(PDO::FETCH_ASSOC);
if (!empty($category)) {
   $select_products = $conn->prepare("SELECT * FROM `products` WHERE `status` = 'active' AND `type` = ? AND `category` = ? ORDER BY id DESC LIMIT 6");
   $select_products->execute([$type, $category]);
   $products = $select_products->fetchAll(PDO::FETCH_ASSOC);
}

?>

<style>
   .logo-container {
      display: flex;
      align-items: center;
      text-decoration: none;
   }

   .store-name {
      font-size: 1.5rem;
      font-weight: bold;
      margin-left: 10px;
      color: #333;
      /* Adjust color */
   }

   .store-logo {
      width: 40px;
      /* Adjust size as needed */
      border-radius: 50%;
      /* Makes it circular */
   }

 
   .header-action-btn{
      display: flex;
      align-items: center;
      gap: 10px;
   }
   .btn-login {
      display: inline-block;
      padding: 10px 20px;
      background-color: darkgreen;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 1.2rem;
      font-weight: bold;
      transition: background-color 0.3s ease-in-out;
   }
   .btn-login:hover {
      background-color: green;
   }
   .btn-register {
      display: inline-block;
      padding: 10px 20px;
      color: darkgreen;
      text-decoration: none;
      border-radius: 5px;
      font-size: 1.2rem;
      font-weight: bold;
      transition: background-color 0.3s ease-in-out;
   }
   .btn-register:hover {
      background-color: #f0f0f0;
      color: darkgreen;
   }
</style>
<header class="header">
   <div class="flex">
      <!-- Store Logo + Name -->
      <div class="logo-container">
         <img src="<?= $store_data[$current_store]['logo']; ?>" alt="Store Logo" class="store-logo">
         <span class="store-name"><?= $store_data[$current_store]['name']; ?></span>
      </div>

      <nav class="navbar">
         <a href="index.php">Home</a>
         <a href="shop.php">Shop</a>
         <a href="about.php">About Us</a>
         <a href="contact.php">Contact</a>
         <?php if ($current_store == 'kape_milagrosa') { ?>
            <a href="#" id="switch-btn">Anak ng Birhen</a>
         <?php } else { ?>
            <a href="#" id="switch-btn">Kape Milagrosa</a>
         <?php } ?>
      </nav>

      <div class="icons">
         <?php if ($user_id) { ?>
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="user-btn" class="fas fa-user"></div>
            <a href="wishlist.php"><i class="fas fa-heart"></i><span>(<?= $count_wishlist_items->rowCount(); ?>)</span></a>
            <a href="cart.php"><i class="fas fa-shopping-cart"></i><span>(<?= $count_cart_items->rowCount(); ?>)</span></a>
            <a href="orders.php"><i class="fas fa-box"></i><span>(<?= $count_orders_items->rowCount(); ?>)</span></a>
         <?php } else { ?>
            <div class="header-action-btn">
               <a href="register.php" class="btn-register">
               Sign up
               </a>
               <span>|</span>
               <a href="login.php" class="btn-login">
                   Login
               </a>
            </div>
         <?php } ?>
      </div>
      <div class="profile">
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="user_profile_update.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
      </div>
   </div>
</header>
<script>
   document.getElementById('switch-btn').addEventListener('click', function (event) {
      event.preventDefault(); // Prevent default link behavior
      fetch('switch_store.php', {
         method: 'POST'
      }).then(() => location.reload());
   });
</script>