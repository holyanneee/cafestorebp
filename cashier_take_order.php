<?php
@include 'config.php';
session_start();

$cashier_name = $_SESSION['cashier_name'] ?? 'Unknown';
$cashier_id = $_SESSION['cashier_id'] ?? null;

if (!$cashier_id) {
    header('location:login.php');
    exit;
}

require('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_data'])) {
    $order_data = json_decode($_POST['order_data'], true);
    $placed_on = date('d-M-Y');

    if (!$order_data || count($order_data) === 0) {
        die('Empty order.');
    }

    $total_price = 0;
    $total_products = [];

    foreach ($order_data as $item) {
        $name = $item['name'];
        $qty = intval($item['qty']);
        $price = floatval($item['price']);
        $total_products[] = "$name ($qty)";
        $total_price += $price * $qty;
    }

    $total_products_str = implode(', ', $total_products);

    // Insert order
    $insert = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, total_products, total_price, placed_on, cashier, receipt, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([
        $cashier_id,
        'Walk-in Customer',
        '',
        '',
        'cash',
        'N/A',
        $total_products_str,
        $total_price,
        $placed_on,
        $cashier_name,
        '',
        'on Queue'
    ]);

    // Get the last inserted order ID
    $order_id = $conn->lastInsertId();

   // Create PDF receipt using small paper size
$pdf = new FPDF('P', 'mm', [90, 200]); // Width: 75mm (~3in)
$pdf->AddPage();
$pdf->SetMargins(5, 5, 5);


// Store Info
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 5, 'Kape Milagrosa', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 4, 'Your Store Address', 0, 1, 'C');
$pdf->Cell(0, 4, 'Phone: (XXX) XXX-XXXX', 0, 1, 'C');
$pdf->Ln(3);

// Cashier & Date
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(0, 4, "Cashier: $cashier_name", 0, 1, 'L');
$pdf->Cell(0, 4, "Date: $placed_on", 0, 1, 'L');
$pdf->Ln(2);

// Order Items
$pdf->SetFont('Courier', '', 9); // Monospaced for receipt look
$total_price = 0;

foreach ($order_data as $item) {
    $name = $item['name'];
    $qty = intval($item['qty']);
    $price = floatval($item['price']);
    $subtotal = $qty * $price;
    $total_price += $subtotal;

    $line = str_pad($name, 18);
    $line .= str_pad('x' . $qty, 5, ' ', STR_PAD_LEFT);
    $line .= str_pad('Php ' . number_format($price, 2), 15, ' ', STR_PAD_LEFT);
    $pdf->Cell(0, 4, $line, 0, 1);
}

// Line separator
$pdf->Ln(2);
$pdf->Cell(0, 0, str_repeat('-', 50), 0, 1);
$pdf->Ln(1);

// Total
$pdf->SetFont('Courier', 'B', 9);
$line = str_pad('TOTAL:', 20);
$line .= str_pad('Php ' . number_format($total_price, 2), 20, ' ', STR_PAD_LEFT);
$pdf->Cell(0, 5, $line, 0, 1);

// Footer
$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 4, 'Thank you for your purchase!', 0, 1, 'C');
$pdf->Cell(0, 4, 'Please visit us again.', 0, 1, 'C');
$pdf->Cell(0, 4, 'For inquiries: (XXX) XXX-XXXX', 0, 1, 'C');

// Save PDF
$receipts_dir = 'receipts/';
if (!is_dir($receipts_dir)) {
    mkdir($receipts_dir, 0755, true);
}

$unique_id = uniqid();
$pdf_filename = $receipts_dir . 'receipt_' . $cashier_id . '_' . date('Ymd_His') . '_' . $unique_id . '.pdf';
$pdf->Output('F', $pdf_filename);

$db_filename = basename($pdf_filename);
$update = $conn->prepare("UPDATE orders SET receipt = ? WHERE id = ?");
$update->execute([$db_filename, $order_id]);

// Redirect to success page
header("Location: cashier_order_placed.php?success=1&pdf=" . urlencode($db_filename));
exit;
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>POS - Take Order</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">

   <style>
      .pos-container {
         display: flex;
         gap: 20px;
         padding: 20px;
         padding-top: 50px;

      }

      .product-section {
         flex: 2;
         overflow-y: auto;
         max-height: 80vh;
      }

      .category-group {
         margin-bottom: 40px;
      }

      .category-title {
         font-size: 1.5rem;
         font-weight: bold;
         margin-bottom: 15px;
         border-bottom: 2px solid #ddd;
         padding-bottom: 5px;
         
      }

      .product-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr); /* Always 3 columns */
        gap: 20px;
      }


      .product-card {
         background: #fff;
         border: 2px solid #ddd;
         padding: 10px;
         height: 320px;
         text-align: center;
         box-shadow: 0 2px 10px rgba(0,0,0,0.05);
         cursor: pointer;
         transition: 0.2s ease;
         display: flex;
         flex-direction: column;
         justify-content: space-between;
         font-size: 1.8rem;
      }

      .product-card:hover {
         border-color: #2ecc71;
         background-color: #f9f9f9;
      }

      .product-card img {
         width: 100%;
         height: 200px;
         object-fit: cover;
      }

      .product-card h4 {
         font-size: 1.9rem;
         margin-top: 10px;
      }

      .product-card p {
         font-weight: bold;
         color: #555;
      }

      .order-cart {
         flex: 1;
         border-left: 2px solid #eee;
         padding-left: 20px;
         max-height: 80vh;
         overflow-y: auto;
      }

      .cart-list {
         margin-bottom: 20px;
      }

      .cart-item {
         display: flex;
         justify-content: space-between;
         align-items: center;
         padding: 8px 0;
         border-bottom: 1px solid #eee;
      }

      .cart-item span {
         font-size: 1.5rem;
         font-weight: 500;
      }


      .remove-btn {
         background: transparent;
         border: none;
         color: red;
         font-size: 1.1rem;
         cursor: pointer;
         margin-left: 10px;
      }
      .remove-btn {
         font-size: 2.4rem;
        padding: 0 5px;
        }


      .total-section {
         font-size: 1.7rem;
         font-weight: bold;
         margin-bottom: 15px;
      }

      .place-order-btn {
         padding: 15px;
         width: 100%;
         background-color: #2ecc71;
         color: white;
         font-size: 1.2rem;
         border: none;
         cursor: pointer;
         transition: 0.3s;
      }

      .place-order-btn:hover {
         background-color: #27ae60;
      }

      /* Styling for the "Add-ons" category */
.add-ons-category .product-card {
   background-color: #f0f0f0; /* Lighter background */
   border: 1px solid #ddd; /* Lighter border */
   box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1); /* Subtle shadow */
   height: 250px; /* Smaller height for add-ons cards */
   font-size: 1.4rem; /* Smaller font size */
   padding: 8px; /* Adjust padding to make the card smaller */
}

.add-ons-category .product-card img {
   height: 150px; /* Smaller image height */
   object-fit: cover; /* Maintain aspect ratio */
}

.add-ons-category .product-card h4 {
   font-size: 1.4rem; /* Smaller font size for the product name */
}

.add-ons-category .product-card p {
   font-size: 1.3rem; /* Smaller price font size */
   color: #666; /* Lighter price color */
}

   </style>
</head>
<body>

<?php include 'cashier_header.php'; ?>

<section class="dashboard">

   <h1 class="title">Cashier - Take Order</h1>

   <div class="pos-container">

      <!-- LEFT: Product Categories -->
      <div class="product-section">

      <?php
   $categories_stmt = $conn->prepare("SELECT DISTINCT category FROM `products` ORDER BY category");
   $categories_stmt->execute();
   $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

   // Separate "Add-ons" category from others
   $addOnsCategory = 'Add-ons';
   $otherCategories = array_filter($categories, fn($category) => $category !== $addOnsCategory);
   $addOnsCategoryExists = in_array($addOnsCategory, $categories);
   
   // Display other categories first
   foreach($otherCategories as $category):
      $products_stmt = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
      $products_stmt->execute([$category]);
      if ($products_stmt->rowCount() > 0):
?>
   <div class="category-group">
      <div class="category-title"><?= htmlspecialchars(ucfirst($category)) ?></div>
      <div class="product-grid">
      <?php while($product = $products_stmt->fetch(PDO::FETCH_ASSOC)): ?>
         <div class="product-card" onclick="addToCart(<?= $product['id']; ?>, '<?= htmlspecialchars($product['name']); ?>', <?= $product['price']; ?>)">
            <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>" alt="">
            <h4><?= htmlspecialchars($product['name']); ?></h4>
            <p>₱<?= number_format($product['price'], 2); ?></p>
         </div>
      <?php endwhile; ?>
      </div>
   </div>
<?php
      endif;
   endforeach;

   // Display "Add-ons" category at the bottom if it exists
   if ($addOnsCategoryExists):
      $products_stmt = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
      $products_stmt->execute([$addOnsCategory]);
      if ($products_stmt->rowCount() > 0):
?>
   <div class="category-group add-ons-category">
      <div class="category-title"><?= htmlspecialchars(ucfirst($addOnsCategory)) ?></div>
      <div class="product-grid">
      <?php while($product = $products_stmt->fetch(PDO::FETCH_ASSOC)): ?>
         <div class="product-card" onclick="addToCart(<?= $product['id']; ?>, '<?= htmlspecialchars($product['name']); ?>', <?= $product['price']; ?>)">
            <img src="uploaded_img/<?= htmlspecialchars($product['image']); ?>" alt="">
            <h4><?= htmlspecialchars($product['name']); ?></h4>
            <p>₱<?= number_format($product['price'], 2); ?></p>
         </div>
      <?php endwhile; ?>
      </div>
   </div>
<?php
      endif;
   endif;
?>


      </div>

      <!-- RIGHT: Cart -->
      <div class="order-cart">
         <h3>Order Summary</h3>
         <div class="cart-list" id="cartList"></div>
         <div class="total-section">Total: ₱<span id="cartTotal">0.00</span></div>
         <form action="cashier_take_order.php" method="POST" id="orderForm">
            <input type="hidden" name="order_data" id="orderData">
            <button type="submit" class="place-order-btn">Place Order</button>
         </form>
      </div>

   </div>

</section>

<script>
   let cart = [];

   function addToCart(id, name, price) {
      const existing = cart.find(item => item.id === id);
      if (existing) {
         existing.qty += 1;
      } else {
         cart.push({ id, name, price, qty: 1 });
      }
      updateCartUI();
   }

   function removeFromCart(id) {
      cart = cart.filter(item => item.id !== id);
      updateCartUI();
   }

   function updateCartUI() {
      const cartList = document.getElementById('cartList');
      const cartTotal = document.getElementById('cartTotal');
      const orderData = document.getElementById('orderData');

      cartList.innerHTML = '';
      let total = 0;

      cart.forEach(item => {
         total += item.price * item.qty;
         cartList.innerHTML += `
            <div class="cart-item">
               <span>${item.name} x${item.qty}</span>
               <span>
                  ₱${(item.price * item.qty).toFixed(2)}
                  <button class="remove-btn" onclick="removeFromCart(${item.id}); event.stopPropagation();">&times;</button>
               </span>
            </div>
         `;
      });

      cartTotal.textContent = total.toFixed(2);
      orderData.value = JSON.stringify(cart);
   }
</script>

<script src="js/script.js"></script>
</body>
</html>
