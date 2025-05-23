<?php 

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit();
}

if (isset($_POST['add_to_wishlist'])) {

    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
    $check_wishlist_numbers->execute([$p_name, $user_id]);

    $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);

    if ($check_wishlist_numbers->rowCount() > 0) {
        $message[] = 'already added to wishlist!';
    } elseif ($check_cart_numbers->rowCount() > 0) {
        $message[] = 'already added to cart!';
    } else {
        $insert_wishlist = $conn->prepare("INSERT INTO `wishlist`(user_id, product_id, name, price, image) VALUES(?,?,?,?,?)");
        $insert_wishlist->execute([$user_id, $product_id, $p_name, $p_price, $p_image]);
        $message[] = 'added to wishlist!';
    }
}

if (isset($_POST['add_to_cart'])) {

    $product_id = filter_var($_POST['product_id'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_name = filter_var($_POST['p_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_price = filter_var($_POST['p_price'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_image = filter_var($_POST['p_image'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $p_qty = filter_var($_POST['p_qty'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    $check_cart_numbers = $conn->prepare("SELECT * FROM `cart` WHERE name = ? AND user_id = ?");
    $check_cart_numbers->execute([$p_name, $user_id]);

    if ($check_cart_numbers->rowCount() > 0) {
        $message[] = 'already added to cart!';
    } else {
        $check_wishlist_numbers = $conn->prepare("SELECT * FROM `wishlist` WHERE name = ? AND user_id = ?");
        $check_wishlist_numbers->execute([$p_name, $user_id]);

        if ($check_wishlist_numbers->rowCount() > 0) {
            $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE name = ? AND user_id = ?");
            $delete_wishlist->execute([$p_name, $user_id]);
        }

        $insert_cart = $conn->prepare("INSERT INTO `cart`(user_id, product_id, name, price, quantity, image) VALUES(?,?,?,?,?,?)");
        $insert_cart->execute([$user_id, $product_id, $p_name, $p_price, $p_qty, $p_image]);
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
   <title>shop</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <style>
.category-buttons {

    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 10px;
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

</style>

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="p-category">

  <div class="category-buttons">
      
      <a href="category.php?category=Frappe" class="category-btn">Frappe</a>
      <a href="category.php?category=Fruit Soda" class="category-btn">Fruit Soda</a>
      <a href="category.php?category=Frappe Extreme" class="category-btn">Frappe Extreme</a>
      <a href="category.php?category=Milk Tea" class="category-btn">Milk Tea</a>
      <a href="category.php?category=Fruit Tea" class="category-btn">Fruit Tea</a>
      <a href="category.php?category=Fruit Milk" class="category-btn">Fruit Milk</a>
      <a href="category.php?category=Espresso" class="category-btn">Espresso</a>
      <a href="category.php?category=Hot Non-Coffee" class="category-btn">Hot Non-Coffee</a>
      <a href="category.php?category=Iced Non-Coffee" class="category-btn">Iced Non-Coffee</a>
      <a href="category.php?category=Meal" class="category-btn">Meal</a>
      <a href="category.php?category=Snacks" class="category-btn">Snacks</a>
    
   </div>

</section>

<section class="products">

   <h1 class="title">latest products</h1>

   <div class="box-container">

   <?php
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE type = 'coffee' AND status = 'active' ORDER BY id DESC");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" class="box" method="POST">
      <div class="price">₱<span><?= $fetch_products['price']; ?></span></div>
      <a href="view_page.php?product_id=<?= $fetch_products['id']; ?>" class="fas fa-eye"></a>
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <div class="name"><?= $fetch_products['name']; ?></div>
      <input type="hidden" name="product_id" value="<?= $fetch_products['id']; ?>">
      <input type="hidden" name="p_name" value="<?= $fetch_products['name']; ?>">
      <input type="hidden" name="p_price" value="<?= $fetch_products['price']; ?>">
      <input type="hidden" name="p_image" value="<?= $fetch_products['image']; ?>">
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