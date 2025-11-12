<?php
 require_once 'enums/OrderStatusEnum.php';
use Enums\OrderStatusEnum;

@include 'config.php';
session_start();

$cashier_name = $_SESSION['cashier_name'] ?? 'Unknown';
$admin_id = $_SESSION['cashier_id'] ?? null;
if (!$admin_id) {
   header('location:login.php');
   exit;
}
$preparingStatus = OrderStatusEnum::Preparing;

// require('fpdf/fpdf.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_data'])) {
   $order_data = json_decode($_POST['order_data'], true);
   $placed_on = date('Y-m-d H:i:s');

   if (!$order_data || count($order_data) === 0) {
      die('Empty order.');
   }

   // check if there are stocks of engredients from ingredients table
   foreach ($order_data as $item) {
      $ingredientChoices = $item['ingredientChoices'] ?? [];
      foreach ($ingredientChoices as $ingredient_id => $ingredient) {
         $ingredient_stmt = $conn->prepare("SELECT stock FROM ingredients WHERE id = ?");
         $ingredient_stmt->execute([$ingredient_id]);
         $ingredient_data = $ingredient_stmt->fetch(PDO::FETCH_ASSOC);

         if (!$ingredient_data) {
            die("Ingredient with ID $ingredient_id not found.");
         }

         // Check if the stock is sufficient
         if ($ingredient_data['stock'] < 1) {
            die("Insufficient stock for ingredient: " . htmlspecialchars($ingredient['name']));
         }
      }
   }

   // process the order 
   $insert = $conn->prepare("INSERT INTO orders (user_id, name, number, email, method, address, placed_on, updated_by_cashier, receipt, is_walk_in, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
   $insert->execute([
      $admin_id,
      'Walk-in Customer',
      '',
      '',
      'cash',
      'N/A',
      $placed_on,
      $cashier_name,
      '',
      true,
      $preparingStatus->value
   ]);

   // Get the last inserted order ID
   $order_id = $conn->lastInsertId();


   foreach ($order_data as $item) {
      // insert into order_products
      $order_product_insert = $conn->prepare("INSERT INTO order_products (order_id,product_id,quantity,price,subtotal,ingredients,cup_sizes,add_ons) VALUES (?,?,?,?,?,?,?,?)");
      $order_product_insert->execute([
         $order_id,
         $item['id'],
         $item['qty'],
         $item['basePrice'],
         ($item['basePrice'] + $item['cupSizePrice'] + $item['addOnsTotal']) * $item['qty'],
         json_encode($item['ingredientChoices']),
         json_encode([
            'size' => $item['cupSize'],
            'price' => $item['cupSizePrice']
         ]),
         json_encode($item['addOns'])
      ]);
   }






   echo "Order successfully placed!";



}
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>POS - Take Order</title>

   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
         grid-template-columns: repeat(3, 1fr);
         gap: 20px;
      }

      .product-card {
         background: #fff;
         border: 2px solid #ddd;
         padding: 10px;
         height: 320px;
         text-align: center;
         box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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

      .cart-item>div {
         flex: 1;
      }

      .cart-item span {
         font-size: 3rem;
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

      /* Add-ons category styling */
      .add-ons-category .product-card {
         background-color: #f0f0f0;
         border: 1px solid #ddd;
         box-shadow: 0 1px 5px rgba(0, 0, 0, 0.1);
         height: 250px;
         font-size: 1.4rem;
         padding: 8px;
      }

      .add-ons-category .product-card img {
         height: 150px;
      }

      .add-ons-category .product-card h4 {
         font-size: 1.4rem;
      }

      .add-ons-category .product-card p {
         font-size: 1.3rem;
         color: #666;
      }

      /* Modal styling */
      .modal-lg {
         max-width: 800px;
      }

      .addon-checkbox {
         margin-right: 10px;
      }

      .text-muted.small {
         font-size: 0.9rem;
         color: #6c757d;
      }

      .cart.text-muted.small {
         font-size: 1.5rem;
      }

      .h3 {
         font-size: 2rem !important;
      }

      /* Slider styling */
      .ingredient-slider {
         margin: 15px 0;
      }

      .slider-container {
         display: flex;
         align-items: center;
         margin-bottom: 10px;
      }

      .slider-label {
         min-width: 120px;
         font-weight: bold;
      }

      .slider-value {
         min-width: 30px;
         text-align: center;
         margin-left: 10px;
      }

      /* Cup size selector */
      .cup-size-selector {
         display: flex;
         gap: 10px;
         margin: 15px 0;
      }

      .cup-size-btn {
         flex: 1;
         padding: 8px;
         border: 2px solid #ddd;
         border-radius: 5px;
         text-align: center;
         cursor: pointer;
         transition: all 0.3s;
      }

      .cup-size-btn:hover {
         border-color: #2ecc71;
      }

      .cup-size-btn.active {
         background-color: #2ecc71;
         color: white;
         border-color: #2ecc71;
      }

      /* custom */
      /* Enhanced Order Summary Section */
      .order-cart {
         flex: 1;
         border-left: 2px solid #eee;
         padding-left: 20px;
         max-height: 80vh;
         overflow-y: auto;
         font-size: 1.1rem;
         /* Increased base font size */
      }

      .order-cart h3 {
         font-size: 1.8rem;
         margin-bottom: 20px;
         color: #2c3e50;
         font-weight: 600;
      }

      .cart-list {
         margin-bottom: 25px;
      }

      .cart-item {
         display: flex;
         justify-content: space-between;
         align-items: flex-start;
         /* Changed to start for better alignment */
         padding: 12px 0;
         border-bottom: 1px solid #eee;
      }

      .cart-item>div:first-child {
         flex: 2;
         /* More space for product info */
      }

      .cart-item>div:last-child {
         flex: 1;
         text-align: right;
         font-weight: 600;
         color: #2c3e50;
      }

      .cart-item strong {
         font-size: 2rem;
         color: #2c3e50;
         display: block;
         margin-bottom: 5px;
      }

      .text-muted.small {
         font-size: 1rem;
         /* Slightly larger */
         color: #7f8c8d;
         line-height: 1.4;
         margin: 3px 0;
      }

      .total-section {
         font-size: 1.5rem;
         font-weight: bold;
         margin: 25px 0;
         color: #2c3e50;
         padding-top: 15px;
         border-top: 2px solid #eee;
      }

      /* Enhanced Modal Styling */
      .modal-lg {
         max-width: 850px;
         /* Slightly wider */
      }

      .modal-header {
         padding: 20px;
         border-bottom: 2px solid #eee;
      }

      .modal-title {
         font-size: 1.8rem;
         font-weight: 600;
         color: #2c3e50;
      }

      .modal-body {
         padding: 25px;
      }

      .modal-body h4 {
         font-size: 1.5rem;
         margin-bottom: 20px;
         color: #2c3e50;
         font-weight: 600;
      }

      .modal-body h5 {
         font-size: 1.3rem;
         margin: 20px 0 15px;
         color: #34495e;
      }

      .list-group-item {
         padding: 12px 20px;
         font-size: 1.1rem;
      }

      .form-check-label {
         font-size: 1.5rem;
         color: #2c3e50;
         margin-right: 10px;
         cursor: pointer;
      }

      /* Quantity Input */
      #productQuantity {
         font-size: 1.2rem;
         padding: 10px;
         width: 100px;
      }

      /* Special Instructions */
      #specialInstructions {
         font-size: 1.1rem;
         padding: 12px;
      }

      /* Cup Size Selector Enhancements */
      .cup-size-selector {
         display: flex;
         gap: 15px;
         margin: 20px 0;
      }

      .cup-size-btn {
         flex: 1;
         padding: 12px;
         border: 2px solid #ddd;
         border-radius: 8px;
         text-align: center;
         cursor: pointer;
         transition: all 0.3s;
         font-size: 1.1rem;
         font-weight: 500;
      }

      /* Slider Enhancements */
      .slider-container {
         margin-bottom: 20px;
      }

      .slider-label {
         min-width: 140px;
         font-weight: 500;
         font-size: 1.1rem;
         color: #34495e;
      }

      .slider-value {
         min-width: 40px;
         font-size: 1.1rem;
         font-weight: 500;
         color: #2c3e50;
      }

      /* Modal Footer */
      .modal-footer {
         padding: 20px;
         border-top: 2px solid #eee;
      }

      .modal-footer .btn {
         font-size: 1.1rem;
         padding: 10px 20px;
      }

      /* Responsive adjustments */
      @media (max-width: 992px) {
         .pos-container {
            flex-direction: column;
         }

         .order-cart {
            border-left: none;
            border-top: 2px solid #eee;
            padding-top: 20px;
            margin-top: 20px;
         }

         .modal-lg {
            max-width: 95%;
         }
      }

      .title {
         font-size: 2.5rem;
         font-weight: 600;
         color: #2c3e50;
         margin-bottom: 20px;
         margin-top: 70px;
         text-align: center;

      }



      .slider-label {
         font-size: 1.5rem;
         color: #2c3e50;
      }
   </style>
</head>

<body>

   <?php include 'cashier_header.php'; ?>

   <section class="dashboard">

      <h1 class="title">Cashier - Take Order</h1>

      <div class="row">

         <!-- LEFT: Product Categories -->
         <div class="col-md-8">

            <?php
            $categories_stmt = $conn->prepare("SELECT DISTINCT category FROM `products` WHERE type='coffee' ORDER BY category");
            $categories_stmt->execute();
            $categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);

            // Separate "Add-ons" category from others
            $addOnsCategory = 'Add-ons';
            $otherCategories = array_filter($categories, fn($category) => $category !== $addOnsCategory);
            $addOnsCategoryExists = in_array($addOnsCategory, $categories);

            // Display other categories first
            foreach ($otherCategories as $category):
               $products_stmt = $conn->prepare("SELECT * FROM `products` WHERE category = ?");
               $products_stmt->execute([$category]);
               if ($products_stmt->rowCount() > 0):
                  ?>
                  <div class="category-group">
                     <div class="category-title"><?= htmlspecialchars(ucfirst($category)) ?></div>
                     <div class="product-grid">
                        <?php while ($product = $products_stmt->fetch(PDO::FETCH_ASSOC)): ?>
                           <div class="product-card" data-id="<?= $product['id'] ?>">
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
            ?>

         </div>

         <div id="productModal" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="modalProductName">Product Name</h5>
                     <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                     <div class="row">
                        <div class="col-md-6">
                           <h4>Customize Your Drink</h4>

                           <!-- Cup Size Selector -->
                           <div class="mb-4">
                              <h5>Cup Size</h5>
                              <div class="cup-size-selector" id="cupSizeSelector">
                                 <!-- Will be populated by JavaScript -->
                              </div>
                           </div>

                           <!-- Ingredient Choices -->
                           <div id="ingredientChoices">
                              <h4>Ingredients</h4>
                              <div id="ingredientContainer"></div>
                           </div>
                        </div>
                        <div class="col-md-6">
                           <h4>Available Add-ons</h4>
                           <div id="addOnsList" class="list-group"></div>
                        </div>
                     </div>
                     <div class="mt-4">
                        <div class="row">
                           <div class="col-md-6">
                              <label for="productQuantity" class="h4">Quantity:</label>
                              <input type="number" id="productQuantity" class="form-control" value="1" min="1">
                           </div>
                        </div>
                        <div class="mt-3">
                           <label for="specialInstructions" class="h4">Special Instructions:</label>
                           <textarea id="specialInstructions" class="form-control" rows="3"
                              placeholder="Any special requests..."></textarea>
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                     <button type="button" class="btn btn-primary" onclick="confirmAddToCart()">
                        <i class="fas fa-cart-plus me-2"></i>Add to Order
                     </button>
                  </div>
               </div>
            </div>
         </div>

         <!-- RIGHT: Cart -->
         <div class="order-cart col-md-4">
            <h3><i class="fas fa-receipt me-2"></i>Order Summary</h3>
            <div class="cart-list" id="cartList">
               <div class="h3 text-muted text-center py-4">Your cart is empty</div>
            </div>
            <div class="total-section">Total: ₱<span id="cartTotal">0.00</span></div>
            <form action="cashier_take_order.php" method="POST" id="orderForm">
               <input type="hidden" name="order_data" id="orderData">
               <button type="submit" class="place-order-btn">
                  <i class="fas fa-paper-plane me-2"></i>Place Order
               </button>
            </form>
         </div>

      </div>


      </div>

   </section>

   <!-- Bootstrap JS Bundle with Popper -->
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <!-- noUiSlider -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.5.0/nouislider.min.js"></script>
   <!-- wNumb for number formatting -->
   <script src="https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.2.0/wNumb.min.js"></script>

   <script>
      let cart = [];
      let selectedProduct = null;
      let modalInstance = null;

      document.addEventListener('DOMContentLoaded', function () {
         modalInstance = new bootstrap.Modal(document.getElementById('productModal'));

         document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function () {
               const productId = this.dataset.id;
               const productName = this.querySelector('h4').textContent;
               const basePrice = parseFloat(this.querySelector('p').textContent.replace('₱', ''));
               openModal(productId, productName, basePrice);
            });
         });
      });

      function openModal(productId, productName, basePrice) {
         selectedProduct = {
            id: productId,
            name: productName,
            basePrice,
            cupSize: 'Regular',
            cupSizePrice: basePrice,
            addOns: [],
            ingredientChoices: {},
            currentPrice: basePrice
         };

         document.getElementById('modalProductName').textContent = productName;
         document.getElementById('productQuantity').value = 1;
         document.getElementById('specialInstructions').value = '';
         document.getElementById('cupSizeSelector').innerHTML = '';
         document.getElementById('ingredientContainer').innerHTML = '';
         document.getElementById('addOnsList').innerHTML = '';

         fetch(`fetch_product_details.php?id=${productId}`)
            .then(res => res.json())
            .then(data => {
               const cupSizeSelector = document.getElementById('cupSizeSelector');
               cupSizeSelector.innerHTML = '';

               console.log(data);

               if (data.cup_sizes && Object.keys(data.cup_sizes).length > 0) {
                  for (const [size, price] of Object.entries(data.cup_sizes)) {
                     const isActive = size.toLowerCase() === 'regular';
                     const displaySize = size.charAt(0).toUpperCase() + size.slice(1);
                     cupSizeSelector.innerHTML += `
                     <div class="cup-size-btn ${isActive ? 'active' : ''}" data-size="${displaySize}" data-price="${price}">
                        <div>${displaySize}</div>
                        <small class="text-muted">₱${parseFloat(price).toFixed(2)}</small>
                     </div>
                  `;
                     if (isActive) {
                        selectedProduct.cupSize = displaySize;
                        selectedProduct.cupSizePrice = parseFloat(price);
                        selectedProduct.currentPrice = parseFloat(price);
                     }
                  }

                  document.querySelectorAll('.cup-size-btn').forEach(btn => {
                     btn.addEventListener('click', function () {
                        document.querySelectorAll('.cup-size-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                        selectedProduct.cupSize = this.dataset.size;
                        selectedProduct.cupSizePrice = parseFloat(this.dataset.price);
                        selectedProduct.currentPrice = selectedProduct.cupSizePrice;
                     });
                  });
               }

               const ingredientContainer = document.getElementById('ingredientContainer');
               if (data.ingredients && data.ingredients.length > 0) {
                  data.ingredients.forEach(ingredient => {
                     const div = document.createElement('div');
                     div.className = 'form-check mb-3';
                     div.innerHTML = `
                     <label class="form-check-label d-block">
                        <strong>${ingredient.name}</strong>
                        <div class="d-flex align-items-center mt-2">
                           ${['Less', 'Regular', 'Extra'].map(level => `
                              <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" 
                                        name="ingredient-${ingredient.id}" 
                                        value="${level}" 
                                        id="ingredient-${ingredient.id}-${level.toLowerCase()}" 
                                        ${level === 'Regular' ? 'checked' : ''}>
                                 <label class="form-check-label" for="ingredient-${ingredient.id}-${level.toLowerCase()}">${level}</label>
                              </div>
                           `).join('')}
                        </div>
                     </label>
                  `;
                     ingredientContainer.appendChild(div);
                  });
               }

               const addOnsList = document.getElementById('addOnsList');
               if (data.addOns && data.addOns.length > 0) {
                  data.addOns.forEach(addOn => {
                     const div = document.createElement('div');
                     div.className = 'list-group-item';
                     div.innerHTML = `
                     <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               value="${addOn.id}" 
                               id="addon-${addOn.id}" 
                               data-price="${addOn.price}">
                        <label class="form-check-label" for="addon-${addOn.id}">
                           ${addOn.name} (+₱${parseFloat(addOn.price).toFixed(2)})
                        </label>
                     </div>
                  `;
                     addOnsList.appendChild(div);
                  });
               }

               modalInstance.show();
            })
            .catch(error => {
               console.error('Error loading product details:', error);
            });
      }

      function confirmAddToCart() {
         const qty = parseInt(document.getElementById('productQuantity').value);
         const note = document.getElementById('specialInstructions').value;

         // check if the user selected a cup size
         const activeCupSizeBtn = document.querySelector('.cup-size-btn.active');
         if (!activeCupSizeBtn) {
            alert('Please select a cup size.');
            return;
         }

         const addOns = Array.from(document.querySelectorAll('#addOnsList input:checked')).map(input => ({
            id: input.value,
            name: input.nextElementSibling.textContent.split(' (+')[0],
            price: parseFloat(input.dataset.price)
         }));

         const ingredientChoices = {};
         document.querySelectorAll('[name^="ingredient-"]:checked').forEach(input => {
            const ingId = input.name.split('-')[1];
            const ingName = input.closest('label').querySelector('strong').textContent;
            const ingLevel = input.value;

            ingredientChoices[ingId] = {
               name: ingName,
               level: ingLevel
            };
         });

         const addOnsTotal = addOns.reduce((sum, a) => sum + a.price, 0);
         const finalPrice = selectedProduct.cupSizePrice + addOnsTotal;

         const cartItem = {
            ...selectedProduct,
            qty,
            price: finalPrice,
            addOns,
            addOnsTotal,
            specialInstructions: note,
            ingredientChoices
         };

         const exists = cart.findIndex(item =>
            item.id === cartItem.id &&
            item.cupSize === cartItem.cupSize &&
            JSON.stringify(item.addOns.map(a => a.id).sort()) === JSON.stringify(cartItem.addOns.map(a => a.id).sort()) &&
            item.specialInstructions === cartItem.specialInstructions &&
            JSON.stringify(item.ingredientChoices) === JSON.stringify(cartItem.ingredientChoices)
         );

         if (exists >= 0) {
            cart[exists].qty += qty;
         } else {
            cart.push(cartItem);
         }

         updateCartUI();
         modalInstance.hide();
      }

      function updateCartUI() {
         const cartList = document.getElementById('cartList');
         const cartTotal = document.getElementById('cartTotal');
         const orderData = document.getElementById('orderData');

         cartList.innerHTML = '';
         let total = 0;

         if (cart.length === 0) {
            cartList.innerHTML = '<div class="h3 text-muted text-center py-4">Your cart is empty</div>';
            cartTotal.textContent = '0.00';
            orderData.value = JSON.stringify([]);
            return;
         }

         console.log(cart);

         cart.forEach((item, index) => {
            const itemTotal = item.qty * (item.basePrice + item.cupSizePrice + item.addOnsTotal);
            total += itemTotal;

            const ingredients = Object.values(item.ingredientChoices)
               .map(choice => `${choice.name}: ${choice.level}`)
               .join(', ');

            cartList.innerHTML += `
            <div class="cart-item">
               <div>
                  <strong class="cart-product">${item.name} (${item.cupSize}) x${item.qty}</strong>
                  <div class="cart text-muted small">Base: ₱${item.basePrice.toFixed(2)} | Cup Size: ₱${item.cupSizePrice.toFixed(2)}</div>
                  ${ingredients ? `<div class="cart text-muted small">${ingredients}</div>` : ''}
                  ${item.addOns.length ? `
                     <div class="cart text-muted small">Add-ons: ${item.addOns.map(a => `${a.name} (+₱${a.price.toFixed(2)})`).join(', ')}</div>` : ''}
                  ${item.specialInstructions ? `<div class="cart text-muted small">Note: ${item.specialInstructions}</div>` : ''}
               </div>
               <div>
                  <div>₱${itemTotal.toFixed(2)}</div>
                  <button class="remove-btn" onclick="removeFromCart(${index}); event.stopPropagation();">&times;</button>
               </div>
            </div>
         `;
         });

         cartTotal.textContent = total.toFixed(2);
         orderData.value = JSON.stringify(cart);
      }

      function removeFromCart(index) {
         cart.splice(index, 1);
         updateCartUI();
      }
   </script>


</body>

</html>