<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}
;

if (isset($_POST['order'])) {

   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $number = $_POST['number'];
   $number = filter_var($number, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $method = $_POST['method'];
   $method = filter_var($method, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $address = 'flat no. ' . $_POST['flat'] . ' ' . $_POST['street'] . ' ' . $_POST['city'] . ' ' . $_POST['state'] . ' ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
   $address = filter_var($address, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $placed_on = date('d-M-Y');

   $cart_total = 0;
   $cart_products[] = '';

   $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
   $cart_query->execute([$user_id]);
   if ($cart_query->rowCount() > 0) {
      while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
         $cart_products[] = $cart_item['name'] . ' ( ' . $cart_item['quantity'] . ' )';
         $sub_total = ($cart_item['price'] * $cart_item['quantity']);
         $cart_total += $sub_total;
      }
      ;
   }
   ;

   $total_products = implode(', ', $cart_products);

   $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
   $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

   if ($cart_total == 0) {
      $message[] = 'your cart is empty';
   } elseif ($order_query->rowCount() > 0) {
      $message[] = 'order placed already!';
   } else {
      $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
      $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);
      $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
      $delete_cart->execute([$user_id]);
      $message[] = 'order placed successfully!';
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
                  <input type="text" name="flat" placeholder="" class="box" required>
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