<?php

@include 'config.php';

session_start();

if (isset($_GET['wishlist_id'])) {
   $wishlist_id = $_GET['wishlist_id'];

   // fetch wishlist item details
   $select_wishlist = $conn->prepare("SELECT * FROM `wishlist` WHERE id = ?");
   $select_wishlist->execute([$wishlist_id]);
   $wishlist_item = $select_wishlist->fetch(PDO::FETCH_ASSOC);

   // check if item exists in cart
   $check_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ? AND product_id = ?");
   $check_cart->execute([$wishlist_item['user_id'], $wishlist_item['product_id']]);

   // if true then update quantity
   if ($check_cart->rowCount() > 0) {
      $update_cart = $conn->prepare("UPDATE `cart` SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
      $update_cart->execute([$wishlist_item['user_id'], $wishlist_item['product_id']]);
   } else {
      // if not then insert into cart
      $insert_cart = $conn->prepare("INSERT INTO `cart` (user_id, product_id, quantity) VALUES (?, ?, 1)");
      $insert_cart->execute([$wishlist_item['user_id'], $wishlist_item['product_id']]);
   }

   // delete from wishlist
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE id = ?");
   $delete_wishlist->execute([$wishlist_id]);

   $message[] = 'Item added to cart and removed from wishlist!';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Wishlist</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

   <style>
      .container {
         min-height: 100vh;
         padding: 2rem;
         display: flex;
         flex-direction: column;
         align-items: center;
      }

      .title {
         font-size: 3rem;
         color: darkgreen;
         margin-top: 5rem;
         margin-bottom: 1rem;
         text-align: center;

      }

      .row {
         display: flex;
         flex-wrap: wrap;
         margin-right: -15px;
         margin-left: -15px;
      }

      .col-md-8,
      .col-md-4 {
         position: relative;
         width: 100%;
         padding-right: 15px;
         padding-left: 15px;
      }

      .col-md-8 {
         flex: 0 0 66.666667%;
         max-width: 66.666667%;
      }

      .col-md-4 {
         flex: 0 0 33.333333%;
         max-width: 33.333333%;
      }

      .empty {
         text-align: center;
         font-size: 1.5rem;
         color: #888;
         margin-top: 2rem;
      }

      .flex {
         display: flex;
      }

      .justify-content-between {
         justify-content: space-between;
      }

      .align-items-center {
         align-items: center;
      }

      .w-full {
         width: 100%;
      }

      .card {
         border: 1px solid #ddd;
         border-radius: 5px;
         margin-bottom: 20px;
         background-color: #fff;
      }

      .card-body {
         padding: 20px;
      }

      .card-footer {
         padding: 10px;
         text-align: center;
         display: flex;
         flex-direction: column;
      }

      .card-title {
         font-size: 1.5rem;
         margin-bottom: 0.5rem;
      }

      .card-text {
         font-size: 1.25rem;
         margin-bottom: 1rem;
      }

      .img-fluid {
         width: 100px;
         height: 100px;
         object-fit: cover;
         padding: 10px;
      }

      .card-summary {
         width: 300px;
      }

      .sumary-title {
         font-size: 2rem;
         text-align: center;
         margin-bottom: 1rem;
         color: darkgreen;
      }

      .add-cart-btn {
         display: inline-block;
         padding: 10px 20px;
         background-color: #28a745;
         color: #fff;
         text-decoration: none;
         border-radius: 5px;
         transition: background-color 0.3s ease;
      }

      .add-cart-btn:hover {
         background-color: #218838;
      }
   </style>
</head>

<body>

   <?php include 'header.php'; ?>
   <?php
   $wishlist_items = [];

   $grand_total = 0;

   // Fetch wishlist items from the database
   $select_wishlist = $conn->prepare("SELECT wishlist.*, products.name, products.price, products.details, products.image FROM `wishlist` 
                                       JOIN `products` ON wishlist.product_id = products.id 
                                       WHERE wishlist.user_id = ? AND wishlist.type = ? ORDER BY wishlist.id DESC");
   $select_wishlist->execute([$user_id, $type]);
   $wishlist_items = $select_wishlist->fetchAll(PDO::FETCH_ASSOC);
   ?>
   <section class="container">

      <h1 class="title">
         wishlist items
      </h1>

      <div class="row">
         <div class="col-md-8">
            <?php if (empty($wishlist_items)) { ?>
               <p class="text-center empty">Your wishlist is empty.</p>
            <?php } else { ?>
               <?php foreach ($wishlist_items as $key => $item) { ?>
                  <div class="card flex">
                     <img src="uploaded_img/<?= $item['image']; ?>" class="img-fluid" alt="<?= $item['name']; ?>">
                     <div class="card-body w-full flex justify-content-between align-items-center">
                        <div>
                           <h5 class="card-title"><?= $item['name']; ?></h5>
                           <p class="card-text">₱ <?= number_format($item['price'], 2); ?></p>
                           <p class="card-text"><?= $item['details'] ?></p>

                        </div>
                        <div>
                           <a href="wishlist.php?wishlist_id=<?= $item['id']; ?>" class="add-cart-btn">
                              Add to Cart
                           </a>
                        </div>
                     </div>
                  </div>
               <?php } ?>
            <?php } ?>
         </div>
         <div class="col-md-4">
            <div class="card card-summary">
               <div class="card-footer">
                  <a href="cart.php?is_going_to_checkout=1"
                     class="btn btn-primary <?= ($grand_total > 0) ? '' : 'disabled'; ?>">
                     Checkout All
                  </a>
                  <a href="cart.php?delete_all"
                     class="btn btn-outline-danger <?= ($grand_total > 0) ? '' : 'disabled'; ?>"
                     onclick="return confirm('Delete all items?');">
                     Delete All
                  </a>

               </div>
            </div>
         </div>
      </div>




   </section>








   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>