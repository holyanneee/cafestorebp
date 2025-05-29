<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

   <style>
      /* remove a tag text design */
      a {
         text-decoration: none;
         color: inherit;
      }

      span.mx-2.text-center {
         font-weight: 600;
         font-size: 1.2rem;
         color: #2c3e50;
      }

      .order-info-btn,
      .remove-btn {
         padding: 5px 10px;
         border-radius: 5px;
         transition: all 0.3s ease;
      }

      .remove-btn:hover {
         background-color: #e74c3c;
         color: white !important;
      }

      .order-info-btn:hover {
         background-color: #2ecc71;
         color: white;
      }
   </style>
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh;">

   <?php include 'header.php'; ?>

   <main style="flex: 1;">
      <div class="container">
         <div class="row mt-5">

            <?php
            $total = 0;
            // Fetch orders items from the database
            $select_orders = $select_orders = $conn->prepare("
            SELECT 
                o.id AS order_id,
                o.name,
                o.email,
                o.placed_on,
                o.payment_status,
                o.type,
                GROUP_CONCAT(op.product_id) AS product_ids,
                (SELECT SUM(op.subtotal) FROM `order_products` op WHERE op.order_id = o.id) AS total_price
            FROM `orders` o 
            LEFT JOIN `order_products` op ON o.id = op.order_id
            WHERE o.user_id = ? AND o.type = ?
            GROUP BY o.id
            ORDER BY o.id
        ");
            $select_orders->execute([$user_id, $type]);
            $orders = $select_orders->fetchAll(PDO::FETCH_ASSOC);
            $count_orders = $select_orders->rowCount();

            $formatted_orders = [];
            foreach ($orders as $order) {
               $formatted_orders[] = [
                  'order_id' => $order['order_id'],
                  'name' => htmlspecialchars($order['name']),
                  'email' => htmlspecialchars($order['email']),
                  'type' => htmlspecialchars($order['type']),
                  'placed_on' => date('M d, Y', strtotime($order['placed_on'])),
                  'payment_status' => htmlspecialchars($order['payment_status']),
                  'total_price' => number_format($order['total_price'], 2),
                  'total_quantity' => array_sum(array_map(function ($product_id) use ($conn, $order) {
                     $select_order_product = $conn->prepare("SELECT quantity FROM `order_products` WHERE order_id = ? AND product_id = ?");
                     $select_order_product->execute([$order['order_id'], $product_id]);
                     $order_product = $select_order_product->fetch(PDO::FETCH_ASSOC);
                     return $order_product ? (int) $order_product['quantity'] : 0;
                  }, explode(',', $order['product_ids']))),
                  'products' => array_filter(array_map(function ($product_id) use ($conn, $order) {
                     // Fetch product details
                     $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                     $select_product->execute([$product_id]);
                     $product = $select_product->fetch(PDO::FETCH_ASSOC);
                     // Check if product exists
                     if (!$product) {
                        return null; // Skip if product not found
                     }

                     // get the quntity and subtotal from order_products
                     $select_order_product = $conn->prepare("SELECT * FROM `order_products` WHERE order_id = ? AND product_id = ?");
                     $select_order_product->execute([$order['order_id'], $product_id]);
                     $order_product = $select_order_product->fetch(PDO::FETCH_ASSOC);
                     if (!$order_product) {
                        return null; // Skip if order product not found
                     }
                     $product['quantity'] = $order_product['quantity'];
                     $product['subtotal'] = $order_product['subtotal'];

                     return [
                        'id' => $product['id'],
                        'name' => htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                        'quantity' => htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'),
                        'price' => number_format($product['price'] ?? 0, 2),
                        'subtotal' => number_format($product['subtotal'] ?? 0, 2),
                        'cup_sizes' => json_decode($order_product['cup_sizes'] ?? '[]', true) ?: [],
                        'ingredients' => array_map(function ($ingredient) {
                        return [
                           'name' => htmlspecialchars($ingredient['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                           'level' => htmlspecialchars($ingredient['level'] ?? '', ENT_QUOTES, 'UTF-8')
                        ];
                     }, json_decode($order_product['ingredients'] ?? '[]', true) ?: []),
                        'add_ons' => array_map(function ($addOn) {
                        return [
                           'name' => htmlspecialchars($addOn['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                           'price' => number_format($addOn['price'] ?? 0, 2)
                        ];
                     }, json_decode($order_product['add_ons'] ?? '[]', true) ?: []),
                     ];
                  }, explode(',', $order['product_ids'])))
               ];
            }

            $orders = $formatted_orders;


            ?>
            <div class="col-lg-12">
               <h5 class="mb-3 h3 text-body">
                  <i class="fas fa-box me-2"></i>
                  Orders
               </h5>
               <hr>
               <div class="d-flex justify-content-between align-items-center mb-4 text-body">
                  <div>
                  </div>
                  <div>
                     <p class="mb-0 h4">You have
                        <?= $count_orders ?> items in your orders
                     </p>
                  </div>
               </div>
               <?php if ($count_orders > 0): ?>
                  <?php foreach ($orders as $item): ?>
                     <div class="card mb-3">
                        <div class="card-body">
                           <div class="d-flex justify-content-between">
                              <div class="d-flex flex-row align-items-center">
                                 <div class="ms-3">
                                    <h3><?= $item['name'] ?></h3>
                                    <h5 class="mb-1">Email: <?= $item['email'] ?></h5>
                                    <h5 class="mb-1">Order ID: <?= $item['order_id'] ?></h5>
                                    <h5 class="mb-1">Placed on: <?= $item['placed_on'] ?></h5>
                                    <h5 class="mb-1">Status:
                                       <?php if ($item['payment_status'] == 'On Queue'): ?>
                                          <span class="badge bg-warning text-dark">Pending</span>
                                       <?php elseif ($item['payment_status'] == 'On Going'): ?>
                                          <span class="badge bg-info text-dark">On Going</span>
                                       <?php elseif ($item['payment_status'] == 'completed'): ?>
                                          <span class="badge bg-success">Completed</span>
                                       <?php else: ?>
                                          <span class="badge bg-danger">Failed</span>
                                       <?php endif; ?>
                                    </h5>
                                 </div>
                              </div>
                              <div class="d-flex flex-row align-items-center">
                                 <div style="width: 50px;">
                                    <h5 class="fw-normal mb-0"><?= $item['total_quantity'] ?></h5>
                                 </div>
                                 <div style="width: 80px;">
                                    <h5 class="mb-0">
                                       ₱<?= number_format((float) str_replace(',', '', $item['total_price']), 2) ?>
                                    </h5>
                                 </div>
                                 <div class="d-flex">
                                    <a href="order_details.php?order_id=<?= $item['order_id'] ?>"
                                       class="order-info-btn text-center">
                                       Order Info.
                                    </a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               <?php else: ?>
                  <div class="card mb-3">
                     <div class="card-body">
                        <h3 class="text-center">Your orders is empty</h3>
                     </div>
                  </div>
               <?php endif; ?>

            </div>

         </div>
      </div>
   </main>



   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>

   <!-- bootstrap js -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



</body>

</html>