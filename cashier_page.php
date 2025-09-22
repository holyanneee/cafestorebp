<?php

@include 'config.php';

session_start();


require_once 'enums/OrderStatusEnum.php';
use Enums\OrderStatusEnum;
$admin_id = $_SESSION['cashier_id'];

if (!isset($admin_id)) {
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Cashier Page</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>

<body>

   <?php include 'cashier_header.php'; ?>

   <section class="dashboard">

      <h1 class="title">dashboard</h1>

      <div class="box-container">

         <div class="box">
            <?php
            $pendingStatus = OrderStatusEnum::Pending;

            $total_pendings = 0;
            $select_pendings = $conn->prepare("SELECT SUM(op.price * op.quantity) AS total_price FROM order_products op JOIN orders o ON op.order_id = o.id WHERE status = ?");
            $select_pendings->execute([$pendingStatus->value]);
            $fetch_pendings = $select_pendings->fetch(PDO::FETCH_ASSOC);
            $total_pendings = $fetch_pendings['total_price'] ?? 0;
            ?>
            <h3>₱<?= $total_pendings; ?></h3>
            <p>total pendings</p>
            <a href="cashier_orders.php" class="btn">see orders</a>
         </div>

         <div class="box">
            <?php
            $total_completed = 0;
            $select_completed = $conn->prepare("SELECT SUM(op.price * op.quantity) AS total_price FROM order_products op JOIN orders o ON op.order_id = o.id WHERE status = ?");
            $select_completed->execute(['completed']);
            while ($fetch_completed = $select_completed->fetch(PDO::FETCH_ASSOC)) {
               $total_completed += $fetch_completed['total_price'];
            }
            ;
            ?>
            <h3>₱<?= $total_completed; ?></h3>
            <p>completed orders</p>
            <a href="cashier_orders.php" class="btn">see orders</a>
         </div>

         <div class="box">
            <?php
            $select_orders = $conn->prepare("SELECT * FROM `orders`");
            $select_orders->execute();
            $number_of_orders = $select_orders->rowCount();
            ?>
            <h3><?= $number_of_orders; ?></h3>
            <p>orders placed</p>
            <a href="cashier_orders.php" class="btn">see orders</a>
         </div>

         <div class="box">
            <?php
            $select_products = $conn->prepare("SELECT * FROM `products`");
            $select_products->execute();
            $number_of_products = $select_products->rowCount();
            ?>
            <h3><?= $number_of_products; ?></h3>
            <p>products added</p>
            <a href="admin_products.php" class="btn">see products</a>
         </div>



      </div>

      <div class="button-container">
         <a href="cashier_take_order.php" class="big-button">Take Order</a>
         <a href="cashier_online_orders.php" class="big-button">Online Order</a>
      </div>


   </section>

   <script src="js/script.js"></script>

</body>

</html>
<style>
   .button-container {
      margin-top: 100px;
      display: flex;
      justify-content: center;
      gap: 60px;
   }

   .big-button {
      padding: 35px 60px;
      font-size: 2.5rem;
      font-weight: bold;
      background-color: #2ecc71;
      color: #fff;
      text-decoration: none;
      border: none;
      border-radius: 0;
      /* sharp corners */
      cursor: pointer;
      transition: background-color 0.3s ease;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
   }

   .big-button:hover {
      background-color: #27ae60;
   }
</style>