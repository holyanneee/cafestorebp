<?php

@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit();
}

// Delete single item
if (isset($_GET['delete'])) {
   $delete_id = $_GET['delete'];
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE id = ?");
   $delete_cart_item->execute([$delete_id]);
   header('location:cart.php');
   exit();
}

// Delete all cart items for user
if (isset($_GET['delete_all'])) {
   $delete_cart_item = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
   $delete_cart_item->execute([$user_id]);
   header('location:cart.php');
   exit();
}

// Update quantity
if (isset($_POST['update_qty'])) {
   $cart_id = $_POST['cart_id'];
   $p_qty = $_POST['p_qty'];
   $p_qty = filter_var($p_qty, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $update_qty = $conn->prepare("UPDATE `cart` SET quantity = ? WHERE id = ?");
   $update_qty->execute([$p_qty, $cart_id]);
   $message[] = 'Cart quantity updated';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Shopping Cart</title>

   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS -->
   <link rel="stylesheet" href="css/style.css">

  <style>
      .button{
         padding: 10px 20px;
         border: none;
         border-radius: 5px;
         cursor: pointer;
         font-size: 16px;
         background: none;
      }
      .button-success {
         background-color: transparent;
         color: #28a745;
         border: 2px solid #28a745;
         transition: all 0.3s ease;
      }
      .button-success:hover, .button-success:focus {
         background-color: #28a745;
         color: #fff;
         outline: none;
      }
      .button-danger {
         background-color: transparent;
         color: #dc3545;
         border: 2px solid #dc3545;
         transition: all 0.3s ease;
      }
      .button-danger:hover, .button-danger:focus {
         background-color: #dc3545;
         color: #fff;
         outline: none;
      }

      .row{
         display: flex;
         flex-wrap: wrap;
         margin-right: -15px;
         margin-left: -15px;
      }
      .col-md-8, .col-md-4 {
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
      .card {
         border: 1px solid #ddd;
         border-radius: 5px;
         margin-bottom: 20px;
         background-color: #fff;
      }
      .card-body {
         padding: 20px;
      }
      .card-title {
         font-size: 1.25rem;
         margin-bottom: 0.5rem;
      }
      .card-text {
         margin-bottom: 1rem;
      }
      .shopping-cart {
         padding: 50px 0;
         background-color: #f8f9fa;
      }
      .shopping-cart h1 {
         font-size: 2rem;
         margin-bottom: 20px;
      }
      .shopping-cart .card {
         margin-bottom: 20px;
      }
      .shopping-cart .card img {
         max-width: 100%;
         height: auto;
         border-radius: 5px;
      }
      .shopping-cart .card-body {
         padding: 20px;
      }

      .shopping-cart .card-body h5 {
         font-size: 1.25rem;
         margin-bottom: 10px;
      }
      input[type="number"] {
         width: 80px;
         padding: 5px;
         border: 1px solid #ccc;
         border-radius: 5px;
         margin-right: 10px;
      }

  </style>
</head>

<body>

   <?php include 'header.php'; ?>

   <section class="shopping-cart py-5">
      <div class="container">
         <h1 class="mb-4">Your Shopping Cart</h1>
         <div class="row">
            <div class="col-md-8">

               <?php
               $grand_total = 0;
               $select_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
               $select_cart->execute([$user_id]);
               $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
               ?>

               <?php if ($select_cart->rowCount() > 0): ?>
                  <?php foreach ($cart_items as $item): ?>
                     <?php $sub_total = $item['price'] * $item['quantity']; ?>
                     <?php $grand_total += $sub_total; ?>

                     <div class="card mb-3" style="max-width: 540px;">
                        <div class="row g-0">
                           <div class="col-md-4">
                              <img src="uploaded_img/<?= $item['image']; ?>" class="img-fluid rounded-start" style="height: 200px; width: 100%; object-fit: cover;"
                                 alt="<?= $item['name']; ?>">
                           </div>
                           <div class="col-md-8">
                              <div class="card-body">
                                 <h5 class="card-title"><?= $item['name']; ?></h5>
                                 <p class="card-text">₱<?= $item['price']; ?> x <?= $item['quantity']; ?> =
                                    <strong>₱<?= $sub_total; ?></strong></p>
                                 <form action="" method="post" class="d-flex align-items-center gap-2">
                                    <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                                    <input type="number" name="p_qty" value="<?= $item['quantity']; ?>" min="1"
                                       class="form-control" style="width: 80px;">
                                       <button type="submit" name="update_qty" class="button button-success">Update</button>
                                       <a href="cart.php?delete=<?= $item['id']; ?>" class="button button-danger"
                                       onclick="return confirm('Remove this item?');">Remove</a>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </div>

                  <?php endforeach; ?>
               <?php else: ?>
                  <p class="text-muted">Your cart is empty.</p>
               <?php endif; ?>
            </div>

            <div class="col-md-4">
               <div class="card">
                  <div class="card-body">
                     <h1>Cart Summary</h1>
                     <h2>Grand Total: <strong>₱<?= $grand_total; ?></strong></h2>
                     <div class="d-grid gap-2">
                        <a href="shop.php" class="btn btn-outline-primary">Continue Shopping</a>
                        <a href="cart.php?delete_all"
                           class="btn btn-outline-danger <?= ($grand_total > 0) ? '' : 'disabled'; ?>"
                           onclick="return confirm('Delete all items?');">Delete All</a>
                        <a href="checkout.php"
                           class="btn btn-success <?= ($grand_total > 0) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </section>

   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>

</body>

</html>