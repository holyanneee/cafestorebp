<?php

@include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
   header('location:login.php');
   exit();
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


   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

   <style>
      /* remove a tag text design */
      a {
         text-decoration: none;
         color: inherit;
      }

      .cart-container {
         display: flex;
         flex-wrap: wrap;
         gap: 20px;
         padding: 20px;
         justify-content: space-between;
      }

      .cart-items {
         flex: 1;
         min-width: 60%;
      }

      .cart-card {
         display: flex;
         border: 1px solid #ccc;
         border-radius: 10px;
         margin-bottom: 15px;
         overflow: hidden;
         background-color: #fff;
      }

      .cart-image {
         width: 120px;
         height: 120px;
         object-fit: cover;
      }

      .cart-details {
         padding: 15px;
         flex: 1;
      }

      .cart-details h3 {
         margin: 0;
         font-size: 18px;
      }

      .cart-details p {
         margin: 5px 0;
      }

      .cart-summary {
         flex-basis: 300px;
         padding: 20px;
         background: #fff;
         border: 1px solid #ccc;
         border-radius: 10px;
         height: fit-content;
      }

      .cart-summary h2 {
         font-size: 20px;
         margin-bottom: 15px;
      }

      .cart-summary .btn {
         width: 100%;
         margin-bottom: 10px;
      }

      .cart-body {
         display: flex;
         width: 100%;
         padding-right: 10px;
         justify-content: space-between;
         align-items: center;
      }

      .cart-customize-btn {
         background-color: #007bff;
         color: white;
         border: none;
         padding: 10px 15px;
         border-radius: 5px;
         cursor: pointer;
      }

      @media screen and (max-width: 768px) {
         .cart-container {
            flex-direction: column;
         }

         .cart-summary {
            width: 100%;
         }
      }


      input[type="number"] {
         width: 80px;
         padding: 5px;
         border: 1px solid #ccc;
         border-radius: 5px;
         margin-right: 10px;
      }
   </style>
   <!-- modal -->
   <style>
      button.add-to-order-btn {
         display: inline-flex;
         align-items: center;
         padding: 10px 20px;
         background-color: #2ecc71;
         color: white;
         font-size: 1.2rem;
         border: none;
         cursor: pointer;
         transition: background-color 0.3s ease;
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

      .cup-size-btn div, .cup-size-btn small{
         font-weight: 600;
      }
      .cup-size-btn div
      {
         font-size: 1.2rem;
      }
      .cup-size-btn small{
         font-size: 1rem;
      }

      .cup-size-btn:hover {
         border-color: #2ecc71;
      }

      .cup-size-btn.active {
         background-color: #2ecc71;
         border-color: #2ecc71;
      }

      .cup-size-btn.active div, .cup-size-btn.active small {
         color: white !important;
      }


      .form-check-label {
         font-size: 1.5rem;
         color: #2c3e50;
         margin-right: 10px;
         cursor: pointer;
      }

      .list-group-item {
         padding: 12px 20px;
         font-size: 1.1rem;
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
   </style>
</head>

<body>

   <?php include 'header.php'; ?>

   <section class="shopping-cart">
      <div class="container">
         <h1 class="title">Your Shopping Cart</h1>
         <div class="cart-container">
            <div class="cart-items">
               <?php
               $grand_total = 0;
               $select_cart = $conn->prepare("SELECT cart.*, products.name, products.price, products.image FROM `cart` 
                                       JOIN `products` ON cart.product_id = products.id 
                                       WHERE cart.user_id = ? AND cart.type = ? ORDER BY cart.id DESC");
               $select_cart->execute([$user_id, $type]);
               $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);
               ?>

               <?php if ($select_cart): ?>
                  <?php foreach ($cart_items as $item): ?>
                     <div class="cart-card">
                        <img src="uploaded_img/<?= $item['image']; ?>" class="cart-image" alt="<?= $item['name']; ?>">
                        <div class="cart-body">
                           <div class="cart-details">
                              <h3><?= $item['name']; ?></h3>
                              <p>₱ <?= $item['price']; ?> x <?= $item['quantity']; ?></p>
                                After customizing, additional details will appear here.
                           </div>
                           <div class="cart-button">
                              <button class="cart-customize-btn" data-id="<?= $item['product_id'] ?>"
                                 data-name="<?= $item['name']; ?>" data-base="<?= $item['price']; ?>"
                                 
                                 >
                                 Customize
                              </button>
                              
                           </div>
                        </div>
                     </div>
                  <?php endforeach; ?>
               <?php else: ?>
                  <p class="text-muted">Your cart is empty.</p>
               <?php endif; ?>

            </div>

            <div class="cart-summary">
               <h2>Cart Summary</h2>
               <p>Grand Total: ₱<?= number_format($grand_total) ?></p>
               <a href="shop.php" class="btn btn-outline-primary">Continue Shopping</a>
               <a href="cart.php?delete_all" class="btn btn-outline-danger" <?php ($grand_total > 0) ? '' : 'disabled'; ?>>Clear Cart</a>
               <a href="checkout.php" class="btn btn-success" <?php ($grand_total > 0) ? '' : 'disabled'; ?>>Proceed To
                  Checkout</a>
            </div>
         </div>
      </div>
   </section>
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
               <button type="button" class="add-to-order-btn" onclick="saveChanges()">
                  Save changes
               </button>
            </div>
         </div>
      </div>
   </div>
   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>
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

         document.querySelectorAll('.cart-customize-btn').forEach(card => {
            card.addEventListener('click', function () {
               const productId = this.dataset.id;
               const productName = this.dataset.name;
               const basePrice = parseFloat(this.dataset.base);
               openModal(productId, productName, basePrice);
            });
         });
      });

      function openModal(productId, productName, basePrice, cupSize, cupSizePrice, ingredientChoices, addOns, quantity, specialInstructions) {

         selectedProduct = {
            id: productId,
            name: productName,
            basePrice: basePrice,
            cupSize: cupSize || 'Regular',
            cupSizePrice: cupSizePrice || 0,
            ingredientChoices: ingredientChoices || {},
            adddOns: addOns || {},
            quantity: quantity || 1,
            specialInstructions: specialInstructions || '',
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

      function saveChanges() {

            updateCartUI();
         modalInstance.hide();
      }

      function updateCartUI() {
         
      }

      function removeFromCart(index) {
         cart.splice(index, 1);
         updateCartUI();
      }
   </script>

</body>

</html>