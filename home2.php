<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['add_to_wishlist'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);

   $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE prod_name = ? AND user_id = ?");
   $check_wishlist_numbers->execute([$p_name, $user_id]);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE prod_name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_wishlist_numbers->rowCount() > 0){
      $message[] = 'already added to wishlist!';
   } elseif($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   } else {
      $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, prod_id, prod_name, prod_price, prod_image) VALUES(?,?,?,?,?)");
      $insert_wishlist->execute([$user_id, $pid, $p_name, $p_price, $p_image]);
      $message[] = 'added to wishlist!';
   }
}

if(isset($_POST['add_to_cart'])){

   $pid = $_POST['pid'];
   $pid = filter_var($pid, FILTER_SANITIZE_STRING);
   $p_name = $_POST['p_name'];
   $p_name = filter_var($p_name, FILTER_SANITIZE_STRING);
   $p_price = $_POST['p_price'];
   $p_price = filter_var($p_price, FILTER_SANITIZE_STRING);
   $p_image = $_POST['p_image'];
   $p_image = filter_var($p_image, FILTER_SANITIZE_STRING);
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_STRING);

   $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE prod_name = ? AND user_id = ?");
   $check_cart_numbers->execute([$p_name, $user_id]);

   if($check_cart_numbers->rowCount() > 0){
      $message[] = 'already added to cart!';
   } else {
      $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE prod_name = ? AND user_id = ?");
      $check_wishlist_numbers->execute([$p_name, $user_id]);

      if($check_wishlist_numbers->rowCount() > 0){
         $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE prod_name = ? AND user_id = ?");
         $delete_wishlist->execute([$p_name, $user_id]);
      }

      $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, prod_id, prod_name, prod_price, quantity, prod_image) VALUES(?,?,?,?,?,?)");
      $insert_cart->execute([$user_id, $pid, $p_name, $p_price, $p_qty, $p_image]);
      $message[] = 'added to cart!';
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>home page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style1.css">
   <style>
.category-buttons {

    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 20px;
}

.category-btn {
    text-decoration: none;
    padding: 12px 20px; /* Increased padding for better appearance */
    border: 2px solid darkgreen; /* Change color as needed */
    border-radius: 15px; /* Changed to make it more rectangular */
    font-weight: bold;
    font-size: 16px; /* Adjusted font size */
    color: black;
    transition: all 0.3s ease-in-out;
}

.category-btn:hover {
    background: darkgreen; /* Adjusted hover color to match border */
    color: white;
}
.home .content {
    color: white;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8); /* Adds a shadow to improve readability */
}

.home .content span,
.home .content h3,
.home .content p {
    color: white;
    text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8);
}

</style>

</head>
<body>
   
<?php include 'header2.php'; ?>


<div class="home-bg">
   <section class="home">
      <div class="content" style="color: white;">
         <span style="color: white;">Faith in Every Piece</span>
         <h3 style="color: white;">Discover Meaningful Religious Items</h3>
         <p style="color: white;">Explore our collection of religious keepsakes, from rosaries to prayer pockets, statues, and more.</p>
         <a href="about.php" class="btn">about us</a>
      </div>
   </section>
</div>


</div>

<section class="home-category">
   <h1 class="title">Shop by Category</h1>
   <div class="category-buttons">

      <a href="category.php?category=Chibi Religious Items" class="category-btn">Chibi Religious Items</a>
      <a href="category.php?category=Angels" class="category-btn">Angels</a>
      <a href="category.php?category=Cross" class="category-btn">Cross</a>
      <a href="category.php?category=Prayer Pocket" class="category-btn">Prayer Pocket</a>
      <a href="category.php?category=Rosary" class="category-btn">Rosary</a>
      <a href="category.php?category=Ref Magnet" class="category-btn">Ref Magnet</a>
      <a href="category.php?category=Keychain" class="category-btn">Keychain</a>
      <a href="category.php?category=Scapular" class="category-btn">Scapular</a>
      <a href="category.php?category=Statues" class="category-btn">Statues</a>

   </div>
</section>


<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE type = 'online' LIMIT 6");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₱<span><?= $fetch_products['prod_price']; ?></span>/-</div>
      <a href="view_page.php?pid=<?= $fetch_products['prod_id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_products['prod_image']; ?>" alt="">
      <div class="name"><?= $fetch_products['prod_name']; ?></div>
      <input type="hidden" name="pid" value="<?= $fetch_products['prod_id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['prod_name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['prod_price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['prod_image']; ?>">
      <input type="number" min="1" value="1" name="p_qty" class="qty">
      <input type="submit" value="add to wishlist" class="option-btn" name="add_to_wishlist">
      <input type="submit" value="add to cart" class="btn" name="add_to_cart">
   </form>
   <?php
      }
   }else{
      echo '<p class="empty">no products added yet!</p>';
   }
   ?>

   </div>

</section>

<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>
