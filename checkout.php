<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}
;

if (isset($_POST['order'])) {

   $name = filter_var($_POST['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $number = filter_var( $_POST['number'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $email = filter_var($_POST['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $method = filter_var($_POST['method'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $address = filter_var($_POST['address'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $placed_on = date('Y-m-d H:i:s');

   // insert to orders
   $order_query = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, placed_on) VALUES(?,?,?,?,?,?,?)");
   $order_query->execute([$user_id, $name, $number, $email, $method, $address, $placed_on]);
   $order_id = $conn->lastInsertId();

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if ($cart_query->rowCount() > 0) {
     foreach ($cart_query->fetchAll(PDO::FETCH_ASSOC) as $cart_item) {
         // insert to order_products
         $prouct_id = $cart_item['product_id'];
         $price = $cart_item['price'];
         $quantity = $cart_item['quantity'];
         $subtotal = $price * $quantity;

         $order_product_query = $conn->prepare("INSERT INTO `order_products`(order_id, product_id, quantity, price, subtotal) VALUES(?,?,?,?,?)");
         $order_product_query->execute([$order_id, $prouct_id, $quantity, $price, $subtotal]);
      }

      // delete from cart
      $delete_cart_query = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart_query->execute([$user_id]);
      $message[] = 'order placed successfully!';
   } else {
      $message[] = 'your cart is empty!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <?php include 'header.php'; ?>

   <section class="display-orders">

      <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if ($select_cart_items->rowCount() > 0) {
         while ($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)) {
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
            ?>
            <p> <?= $fetch_cart_items['name']; ?>
               <span>(<?= '₱' . $fetch_cart_items['price'] . ' x ' . $fetch_cart_items['quantity']; ?>)</span>
            </p>
            <?php
         }
      } else {
         echo '<p class="empty">your cart is empty!</p>';
      }
      ?>
      <div class="grand-total">grand total : <span>₱<?= $cart_grand_total; ?></span></div>
      <section class="checkout-orders">


         <form action="" method="POST">

            <h3>Place your order</h3>

            <div class="flex">
               <div class="inputBox">
                  <span>Name :</span>
                  <input type="text" name="name" placeholder="" class="box" required>
               </div>
               <div class="inputBox">
                  <span>Number :</span>
                  <input type="number" name="number" placeholder="" class="box" required>
               </div>
               <div class="inputBox">
                  <span>Email :</span>
                  <input type="email" name="email" placeholder="" class="box" required>
               </div>
               <div class="inputBox">
                  <span>Payment Method :</span>
                  <select name="method" class="box" id="paymentMethod" required>
                     <option value="">Select Payment Method</option>
                     <option value="cash on delivery">Cash on Delivery</option>
                     <option value="gcash">Gcash</option>
                     <option value="paypal">Paypal</option>
                  </select>
               </div>
               <div class="inputBox">
                  <span>Address :</span>
                  <input type="text" name="address" placeholder="" class="box" required>
               </div>
               <div class="inputBox">
                  <span>City :</span>
                  <input type="text" name="city" placeholder="" class="box" required>
               </div>
               <div class="inputBox">
                  <span>Zip Code :</span>
                  <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
               </div>
            </div>

            <!-- QR Code Display Section -->
            <div id="paymentQRCode" style="display: none; text-align: center;">
               <p id="qrText" style="margin-bottom: 5px;"></p>
               <img id="qrImage" src="" alt="Payment QR Code" style="max-width: 200px;">
            </div>


            <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1) ? '' : 'disabled'; ?>"
               value="place order">

         </form>


      </section>




      <script src="js/script.js"></script>
      <script>
         document.getElementById('paymentMethod').addEventListener('change', function () {
            var qrCodeSection = document.getElementById('paymentQRCode');
            var qrImage = document.getElementById('qrImage');
            var qrText = document.getElementById('qrText');

            if (this.value === "gcash") {
               qrCodeSection.style.display = "block";
               qrImage.src = "images/gcash.png"; // Replace with actual GCash QR image path
               qrText.innerText = "Scan the QR code below to complete payment.";
            } else if (this.value === "paypal") {
               qrCodeSection.style.display = "block";
               qrImage.src = "images/paypal.jpg"; // Replace with actual PayPal QR image path
               qrText.innerText = "Scan the QR code below to complete payment.";
            } else {
               qrCodeSection.style.display = "none";
               qrImage.src = "";
               qrText.innerText = "";
            }
         });

      </script>


</body>

</html>