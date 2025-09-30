<?php
@include 'config.php';
session_start();


if (empty($_SESSION['user_id'])) {
   header('location:login.php');
   exit;
}
if (empty($_GET['type'])) {
   header('location: cart.php');
   exit;
}

$type = $_GET['type'];

$user_id = (int) $_SESSION['user_id'];


$select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
$select_user->execute([$user_id]);
$user = $select_user->fetch(PDO::FETCH_ASSOC);

$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {

   try {
      // Start transaction
      $conn->beginTransaction();

      $name = trim($_POST['name'] ?? '');
      $number = trim($_POST['number'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $method = trim($_POST['method'] ?? '');
      $address = trim($_POST['address'] ?? '');

      $stmt = $conn->prepare("
           SELECT 
               c.*, 
               p.name, p.price, p.image, p.type
           FROM cart c
           JOIN products p ON c.product_id = p.id
           WHERE c.user_id = ?
       ");
      $stmt->execute([$user_id]);
      $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

      if (!$cartItems) {
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Your cart is empty!'];
         header('location: cart.php');
         exit;
      }



      $stmt = $conn->prepare("
           INSERT INTO orders 
               (user_id, name, number, email, method, address, type, placed_on) 
           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
       ");
      $stmt->execute([$user_id, $name, $number, $email, $method, $address, $type]);
      $orderId = $conn->lastInsertId();

      $isCoffee = ($type === 'coffee');
      $query = $type === 'coffee'
         ? "INSERT INTO order_products 
                (order_id, product_id, quantity, price, subtotal, ingredients, cup_sizes, add_ons)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
         : "INSERT INTO order_products 
                (order_id, product_id, quantity, price, subtotal)
              VALUES (?, ?, ?, ?, ?)";
      $stmt = $conn->prepare($query);

      foreach ($cartItems as $item) {
         if ($isCoffee) {
            $cup_size = json_decode($item['cup_size'] ?? '[]', true);
            $ingredients = json_decode($item['ingredients'] ?? '[]', true);
            $add_ons = json_decode($item['add_ons'] ?? '[]', true);

            $stmt->execute([
               $orderId,
               $item['product_id'],
               $item['quantity'],
               $item['price'],
               $item['subtotal'],
               json_encode($ingredients, JSON_UNESCAPED_UNICODE),
               json_encode($cup_size, JSON_UNESCAPED_UNICODE),
               json_encode($add_ons, JSON_UNESCAPED_UNICODE),
            ]);
         } else {
            $stmt->execute([
               $orderId,
               $item['product_id'],
               $item['quantity'],
               $item['price'],
               $item['subtotal'],
            ]);
         }
      }

      $conn->prepare("DELETE FROM cart WHERE user_id = ? AND type = ?")
         ->execute([$user_id, $type]);

      $conn->commit();

      ob_clean(); // Clean output buffer for JSON response
      $_SESSION['alert'] = ['type' => 'success', 'message' => 'Order placed successfully!'];
      header('location:orders.php');
      exit;

   } catch (Exception $e) {
      // Rollback on any error
      if ($conn->inTransaction()) {
         $conn->rollBack();
      }

      // Respond with error JSON
      ob_clean();
      // $_SESSION['alert'] = ['type' => 'error', 'message' => 'Failed to place order. Please try again.'];
      // for debugging purposes, we can show the actual error message
      // $_SESSION['alert'] = ['type' => 'error', 'message' => $e->getMessage()];
      // header('location: checkout.php');
      echo json_encode([
         'success' => false,
         'message' => 'Something went wrong while placing your order. Please try again.',
         'error' => $e->getMessage() // remove in production
      ]);

      exit;
   }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Checkout | Kape Milagrosa
   </title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

   <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="min-h-screen font-sans text-gray-900">
   <?php include 'header.php'; ?>

   <main>
      <section class="mt-30">
         <div class="max-w-4xl mx-auto mb-10 p-6 bg-white rounded-lg shadow-md">
            <h2 class="text-2xl font-bold mb-6 text-center text-color">Checkout</h2>
            <form action="" method="POST" class="space-y-4">
               <input type="hidden" name="checkout" value="1">
               <div>
                  <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                  <input type="text" name="name" id="name" required value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                     class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
               </div>
               <div>
                  <label for="number" class="block text-sm font-medium text-gray-700">Phone Number</label>
                  <input type="text" name="number" id="number" required
                     value="<?= htmlspecialchars($user['number'] ?? '') ?>"
                     class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
               </div>
               <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <input type="email" name="email" id="email" required
                     value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                     class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
               </div>
               <div>
                  <label for="method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                  <select name="method" id="method" required
                     class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500">
                     <option value="">Select a payment method</option>
                     <option value="cash on delivery">Cash on Delivery</option>
                     <option value="gcash">GCash</option>
                     <option value="paypal">PayPal</option>
                  </select>
               </div>
               <div id="qrcode" class="text-center">

               </div>
               <div>
                  <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                  <textarea name="address" id="address" rows="3" required
                     class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-indigo-500 focus:border-indigo-500"></textarea>
               </div>
               <div class="flex justify-end">
                  <button type="submit" name="checkout"
                     class="inline-block bg-color text-white px-6 py-2 rounded-md hover:bg-hover-color focus:outline-none focus:ring-2 focus:ring-indigo-500">
                     Place Order
                  </button>
               </div>
            </form>
         </div>

      </section>
   </main>
   <?php include 'footer.php'; ?>

   <!-- sweet alert -->
   <script src=" https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
   <?php if (!empty($alert)): ?>
      <script>
         document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
               icon: '<?= $alert['type'] ?>',
               title: '<?= $alert['message'] ?>',
               showConfirmButton: false,
               timer: 1500
            });
         });
      </script>
   <?php endif; ?>

   <script>
      document.addEventListener('DOMContentLoaded', () => {
         const methodSelect = document.getElementById('method');
         const qrCodeDiv = document.getElementById('qrcode');

         methodSelect.addEventListener('change', () => {
            const selectedMethod = methodSelect.value;
            const message = document.createElement('p');

            message.classList.add('text-center', 'text-sm', 'text-gray-600');
            qrCodeDiv.innerHTML = '';

            if (selectedMethod === 'gcash') {
               const qrImg = document.createElement('img');
               qrImg.src = "images/gcash.png";
               qrImg.alt = 'GCash QR Code';
               qrImg.classList.add('w-32', 'h-32', 'mx-auto', 'my-4');
               qrCodeDiv.appendChild(qrImg);

               message.textContent = 'Scan the GCash QR code to pay.';
            } else if (selectedMethod === 'paypal') {
               const qrImg = document.createElement('img');
               qrImg.src = "images/paypal.jpg";
               qrImg.alt = 'PayPal QR Code';
               qrImg.classList.add('w-32', 'h-32', 'mx-auto', 'my-4');
               qrCodeDiv.appendChild(qrImg);

               message.textContent = 'Scan the PayPal QR code to pay.';
            } else {
               message.textContent = '';
            }
            qrCodeDiv.appendChild(message);
         });
      });
   </script>
</body>

</html>