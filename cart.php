<?php

@include 'config.php';
session_start();

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

      span.mx-2.text-center {
         font-weight: 600;
         font-size: 1.2rem;
         color: #2c3e50;
      }

      .customize-btn,
      .remove-btn {
         padding: 5px 10px;
         border-radius: 5px;
         transition: all 0.3s ease;
      }

      .remove-btn:hover {
         background-color: #e74c3c;
         color: white !important;
      }

      .customize-btn:hover {
         background-color: #2ecc71;
         color: white;
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

      .cup-size-btn div,
      .cup-size-btn small {
         font-weight: 600;
      }

      .cup-size-btn div {
         font-size: 1.2rem;
      }

      .cup-size-btn small {
         font-size: 1rem;
      }

      .cup-size-btn:hover {
         border-color: #2ecc71;
      }

      .cup-size-btn.active {
         background-color: #2ecc71;
         border-color: #2ecc71;
      }

      .cup-size-btn.active div,
      .cup-size-btn.active small {
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

<body style="display: flex; flex-direction: column; min-height: 100vh;">

   <?php include 'header.php'; ?>

   <main style="flex: 1;">
      <div class="container">
         <div class="row mt-5">

            <?php
            $total = 0;
            $select_cart = $conn->prepare("SELECT cart.*, products.name, products.price, products.image, products.cup_sizes as product_cup_sizes FROM `cart` 
                                 JOIN `products` ON cart.product_id = products.id 
                                 WHERE cart.user_id = ? AND cart.type = ? ORDER BY cart.id DESC");
            $select_cart->execute([$user_id, $type]);
            $cart_items = $select_cart->fetchAll(PDO::FETCH_ASSOC);

            $count_cart_items = $select_cart->rowCount();
            ?>
            <div class="col-lg-7">
               <h5 class="mb-3 h3"><a href="shop.php" class="text-body"><i
                        class="fas fa-long-arrow-alt-left me-2"></i>Continue
                     shopping</a></h5>
               <hr>

               <div class="d-flex justify-content-between align-items-center mb-4">
                  <div>
                     <p class="mb-1 h4">Shopping cart</p>

                  </div>
                  <div>
                      <p class="mb-0 h4">You have
                        <?= $count_cart_items ?> items in your cart
                      </p>
                    </div>
                  </div>
                  <?php if ($count_cart_items > 0): ?>
                    <?php foreach ($cart_items as $item): ?>
                      <?php
                     $product_cup_sizes = isset($item['product_cup_sizes']) ? json_decode($item['product_cup_sizes'], true) : [];
                     $cup_size = json_decode($item['cup_size'], true)['size'];
                     
                     $cup_size_price = json_decode($item['cup_size'], true)['price'];
                     

                     $ingredient_choices = isset($item['ingredients']) ? (json_decode($item['ingredients'], true)) : [];
                     $add_ons = isset($item['add_ons']) ? (json_decode($item['add_ons'], true)) : [];
                     $quantity = $item['quantity'] ?? 1;
                     $special_instructions = isset($item['special_instructions']) ? htmlspecialchars($item['special_instructions'], ENT_QUOTES) : '';
                     ?>
                     <div class="card mb-3">
                        <div class="card-body">
                           <div class="d-flex justify-content-between">
                              <div class="d-flex flex-row align-items-center">
                                 <div>
                                    <img src="uploaded_img/<?= $item['image'] ?>" class="img-fluid rounded-3"
                                       alt="Shopping item" style="width: 65px; height: 100px;">
                                 </div>
                                 <div class="ms-3">
                                    <h4><?= $item['name'] ?></h4>
                                    <!-- other details -->
                                    <p class="h5 mb-0">Cup Size: <?= $cup_size ?>
                                       (₱<?= number_format($cup_size_price, 2) ?>)</p>
                                    <?php if (!empty($ingredient_choices)): ?>
                                       <p class="h5 mb-0">
                                          Ingredients:
                                          <?php foreach ($ingredient_choices as $ingredient): ?>
                                             <?= htmlspecialchars($ingredient['name']) ?>
                                             (<?= htmlspecialchars($ingredient['level']) ?>),
                                          <?php endforeach; ?>

                                       </p>
                                    <?php endif; ?>
                                    <?php if (!empty($add_ons)): ?>
                                       <p class="h5 mb-0">
                                          Add-ons:
                                          <?php foreach ($add_ons as $add_on): ?>
                                             <?= htmlspecialchars($add_on['name']) ?> (₱<?= number_format($add_on['price'], 2) ?>),
                                          <?php endforeach; ?>
                                       </p>
                                    <?php endif; ?>
                                    <?php if (!empty($special_instructions)): ?>
                                       <p class="h5 mb-0">
                                          Instruction:
                                          <?= htmlspecialchars($special_instructions) ?>
                                       </p>
                                    <?php endif ?>
                                 </div>
                              </div>
                              <div class="d-flex flex-row align-items-center">
                                 <div style="width: 50px;">
                                    <h5 class="fw-normal mb-0"><?= $item['quantity'] ?></h5>
                                 </div>
                                 <div style="width: 80px;">
                                    <h5 class="mb-0">
                                       ₱<?= number_format(($item['price'] + $cup_size_price) * $item['quantity'], 2) ?>
                                    </h5>
                                 </div>

                                 <div class="d-flex justify-content-between align-items-center">
                                    <a href="#!" class="text-muted remove-btn" data-id="<?= $item['id'] ?>">Remove</a>
                                    <span class="mx-2 text-center">|</span>
                                    <a href="#!" class="customize-btn" data-id="<?= $item['id'] ?>"
                                       data-product-id="<?= $item['product_id'] ?>"
                                       data-name="<?= htmlspecialchars($item['name']) ?>" data-base="<?= $item['price'] ?>"
                                       data-cup-size="<?= $cup_size ?>" data-cup-size-price="<?= $cup_size_price ?>"
                                       data-ingredient-choices='<?= json_encode($ingredient_choices) ?>'
                                       data-add-ons='<?= json_encode($add_ons) ?>' data-quantity="<?= $quantity ?>"
                                       data-special-instructions="<?= $special_instructions ?>">
                                       Customize
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
                        <h3 class="text-center">Your cart is empty</h3>
                     </div>
                  </div>
               <?php endif; ?>

            </div>
            <div class="col-lg-5">

               <div class="card bg-success text-white rounded-3 mt-5">
                  <div class="card-body">
                     <!-- display all products on cart with name, quantity, and price -->
                     <h3 class="mb-3 text-center">Cart Summary</h3>
                     <div class="d-flex flex-column mb-4">
                        <?php foreach ($cart_items as $item) { ?>
                           <?php
                           $cup_size = isset($item['cup_size']) ? (json_decode($item['cup_size'], true)['size']) : 'Regular';
                           $cup_size_price = isset($item['cup_size']) ? (json_decode($item['cup_size'], true)['price']) : 0;
                           $ingredient_choices = isset($item['ingredients']) ? (json_decode($item['ingredients'], true)) : [];
                           $add_ons = isset($item['add_ons']) ? (json_decode($item['add_ons'], true)) : [];
                           $special_instructions = isset($item['special_instruction']) ? htmlspecialchars($item['special_instruction'], ENT_QUOTES) : '';
                           $total = $total + ($item['price'] + $cup_size_price) * $item['quantity'];
                           ?>
                           <div class="d-flex align-items-center justify-content-between mb-2">
                              <p class="h4 text-white"><?= $item['name'] ?> (<?= $cup_size ?>) x <?= $item['quantity'] ?>
                              </p>
                              <p class="h4 text-white">
                                 ₱<?= number_format(($item['price'] + $cup_size_price) * $item['quantity'], 2) ?>
                              </p>
                           </div>
                        <?php } ?>
                     </div>
                     <div class="d-flex justify-content-between mb-4">
                        <p class="h4 mb-2 text-white">Total</p>
                        <p class="h4 mb-2 text-white">₱<?= number_format($total, 2) ?></p>
                     </div>

                     <button type="button" id="checkout" class="btn btn-info btn-block btn-lg">
                        <div class="d-flex justify-content-between">
                           <span>₱<?= number_format($total, 2) ?></span>
                           <span>Checkout <i class="fas fa-long-arrow-alt-right ms-2"></i></span>
                        </div>
                     </button>

                  </div>
               </div>

            </div>

         </div>
      </div>
   </main>
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
   <!-- Checkout Modal -->
   <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
      <div class="modal-dialog">
         <form id="checkoutForm" class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="checkoutModalLabel">Checkout Details</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
               <div class="mb-3">
                  <label for="checkoutName" class="form-label">Name</label>
                  <input type="text" class="form-control" id="checkoutName" name="name" value="<?= $fetch_profile['name']?>" required>
               </div>
               <div class="mb-3">
                  <label for="checkoutNumber" class="form-label">Phone Number</label>
                  <input type="text" class="form-control" id="checkoutNumber" name="number" required>
               </div>
               <div class="mb-3">
                  <label for="checkoutEmail" class="form-label">Email</label>
                  <input type="email" class="form-control" id="checkoutEmail" name="email" value="<?= $fetch_profile['email']?>" required>
               </div>
               <div class="mb-3">
                  <label for="checkoutMethod" class="form-label">Payment Method</label>
                  <select class="form-select" id="checkoutMethod" name="method" required>
                     <option value="Cash on Delivery">Cash on Delivery</option>
                     <option value="GCash">GCash</option>
                     <option value="Paypal">Paypal</option>
                  </select>
               </div>
               <div class="mb-3">
                  <label for="checkoutAddress" class="form-label">Delivery Address</label>
                  <textarea class="form-control" id="checkoutAddress" name="address" rows="2" required></textarea>
               </div>
               <!-- QR Code Display Section -->
               <div id="paymentQRCode" style="display: none; text-align: center;">
                  <p id="qrText" style="margin-bottom: 5px;"></p>
                  <img id="qrImage" src="" alt="Payment QR Code" style="max-width: 200px;">
               </div>
            </div>
            <div class="modal-footer">
               <button type="submit" class="btn btn-primary">Confirm Checkout</button>
            </div>
         </form>
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

         document.querySelectorAll('.customize-btn').forEach(card => {
            card.addEventListener('click', function () {
               const id = this.dataset.id;
               const productId = this.dataset.productId;
               const productName = this.dataset.name;
               const basePrice = parseFloat(this.dataset.base);
               const cupSize = this.dataset.cupSize;
               const cupSizePrice = parseFloat(this.dataset.cupSizePrice);
               const ingredientChoices = JSON.parse(this.dataset.ingredientChoices || '{}');
               const addOns = JSON.parse(this.dataset.addOns || '{}');
               const quantity = parseInt(this.dataset.quantity) || 1;
               const specialInstructions = this.dataset.specialInstructions;

               // Open the modal with the product details
               openModal(id, productId, productName, basePrice, cupSize, cupSizePrice, ingredientChoices, addOns, quantity, specialInstructions);
            });
         });

         document.querySelectorAll('.remove-btn').forEach(element => {
            element.addEventListener('click', function () {
               const id = this.dataset.id;

               removeFromCart(id);
            });
         });

         document.getElementById('checkout').addEventListener('click', function () {
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutModal'));
            checkoutModal.show();
         });
         document.getElementById('checkoutForm').addEventListener('submit', function (e) {
            e.preventDefault();

            const phone = document.getElementById('checkoutNumber').value.trim();

            // Accepts: 09XXXXXXXXX or +639XXXXXXXXX
            const phoneRegex = /^(09\d{9}|\+639\d{9})$/;

            if (!phoneRegex.test(phone)) {
               alert('Please enter a valid Philippine phone number (e.g., 09123456789 or +639123456789)');
               return;
            }

            const formData = new FormData(this);
            formData.append('action', 'checkout');

            fetch('update_cart.php', {
               method: 'POST',
               body: formData
            })
               .then(response => response.json())
               .then(data => {
                  if (data.success) {
                     alert('Order placed successfully!');
                     window.location.reload(); // or redirect
                  } else {
                     alert('Checkout failed: ' + data.message);
                  }
               })
               .catch(error => {
                  console.error('Checkout error:', error);
                  alert('Something went wrong!');
               });
         });

         document.getElementById('checkoutMethod').addEventListener('change', function () {
            var qrCodeSection = document.getElementById('paymentQRCode');
            var qrImage = document.getElementById('qrImage');
            var qrText = document.getElementById('qrText');


            if (this.value === "GCash") {
               qrCodeSection.style.display = "block";
               qrImage.src = "images/gcash.png"; // Replace with actual GCash QR image path
               qrText.innerText = "Scan the QR code below to complete payment.";
            } else if (this.value === "Paypal") {
               qrCodeSection.style.display = "block";
               qrImage.src = "images/paypal.jpg"; // Replace with actual PayPal QR image path
               qrText.innerText = "Scan the QR code below to complete payment.";
            } else {
               qrCodeSection.style.display = "none";
               qrImage.src = "";
               qrText.innerText = "";
            }
         });
      });


      function openModal(id, productId, productName, basePrice, cupSize, cupSizePrice, ingredientChoices, addOns, quantity, specialInstructions) {
         selectedProduct = {
            id: id, // Store the cart ID for updates
            productId: productId,
            name: productName,
            basePrice: basePrice,
            cupSize: cupSize || 'Regular',
            cupSizePrice: cupSizePrice || 0,
            ingredientChoices: ingredientChoices || {},
            addOns: addOns || {},
            quantity: quantity || 1,
            specialInstructions: specialInstructions || '',
         };

         document.getElementById('modalProductName').textContent = productName;
         document.getElementById('productQuantity').value = selectedProduct.quantity;
         document.getElementById('specialInstructions').value = selectedProduct.specialInstructions;

         document.getElementById('cupSizeSelector').innerHTML = '';
         document.getElementById('ingredientContainer').innerHTML = '';
         document.getElementById('addOnsList').innerHTML = '';

         fetch(`fetch_product_details.php?id=${productId}`)
            .then(res => res.json())
            .then(data => {
               const cupSizeSelector = document.getElementById('cupSizeSelector');

               if (data.cup_sizes && Object.keys(data.cup_sizes).length > 0) {
                  for (const [size, price] of Object.entries(data.cup_sizes)) {
                     const displaySize = size.charAt(0).toUpperCase() + size.slice(1);
                     const isActive = displaySize === selectedProduct.cupSize;
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

                     // Pre-select stored ingredient level from nested object
                     const selectedIngredient = selectedProduct.ingredientChoices?.[ingredient.id];
                     const selectedLevel = selectedIngredient?.level;
                     if (selectedLevel) {
                        const radio = div.querySelector(`input[type="radio"][value="${selectedLevel}"]`);
                        if (radio) radio.checked = true;
                     }
                  });

               }

               const addOnsList = document.getElementById('addOnsList');
               if (data.addOns && data.addOns.length > 0) {
                  data.addOns.forEach(addOn => {
                     const div = document.createElement('div');
                     div.className = 'list-group-item';

                     const isChecked = Array.isArray(selectedProduct.addOns)
                        ? selectedProduct.addOns.some(a => String(a.id) === String(addOn.id))
                        : false;



                     div.innerHTML = `
                        <div class="form-check">
                           <input class="form-check-input" type="checkbox" 
                                 value="${addOn.id}" 
                                 id="addon-${addOn.id}" 
                                 data-price="${addOn.price}"
                                 ${isChecked ? 'checked' : ''}>
                           <label class="form-check-label" for="addon-${addOn.id}">
                              ${addOn.name.trim()} (+₱${parseFloat(addOn.price).toFixed(2)})
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
         if (!selectedProduct) {
            console.error('No product selected');
            return;
         }

         // Capture cup size – already handled during click

         // Capture quantity
         selectedProduct.quantity = parseInt(document.getElementById('productQuantity').value) || 1;

         // Capture special instructions
         selectedProduct.specialInstructions = document.getElementById('specialInstructions').value.trim();

         // Capture ingredient choices
         selectedProduct.ingredientChoices = {};
         document.querySelectorAll('#ingredientContainer .form-check-input[type="radio"]:checked').forEach(radio => {
            const [_, id] = radio.name.split('ingredient-');
            selectedProduct.ingredientChoices[id] = {
               name: radio.closest('label').querySelector('strong').textContent.trim(),
               level: radio.value
            };
         });

         // Capture add-ons
         selectedProduct.addOns = [];
         document.querySelectorAll('#addOnsList .form-check-input[type="checkbox"]:checked').forEach(checkbox => {
            const label = checkbox.closest('.form-check').querySelector('label').textContent.trim();
            selectedProduct.addOns.push({
               id: checkbox.value,
               name: label.replace(/\(\+₱.*\)/, '').trim(),
               price: parseFloat(checkbox.dataset.price)
            });
         });

         // Submit to backend
         const formData = new FormData();
         formData.append('id', selectedProduct.id); // Include cart ID for updates
         formData.append('product_id', selectedProduct.productId);
         formData.append('quantity', selectedProduct.quantity);
         formData.append('cup_size', JSON.stringify({
            size: selectedProduct.cupSize,
            price: selectedProduct.cupSizePrice
         }));
         formData.append('subtotal', (selectedProduct.basePrice + selectedProduct.cupSizePrice) * selectedProduct.quantity);
         formData.append('ingredients', JSON.stringify(selectedProduct.ingredientChoices));
         formData.append('add_ons', JSON.stringify(selectedProduct.addOns));
         formData.append('special_instructions', selectedProduct.specialInstructions);

         fetch('update_cart.php', {
            method: 'POST',
            body: formData
         })
            .then(response => response.json())
            .then(data => {
               if (data.success) {
                  alert('Changes saved successfully!');
                  updateCartUI(); // You may want to reload cart items here.
                  modalInstance.hide();
               } else {
                  alert('Failed to save changes: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error:', err);
               alert('An error occurred while saving changes.');
            });
      }


      function updateCartUI() {
         location.reload()
      }

      function removeFromCart(id) {
         const formData = new FormData();
         formData.append('cart_id', id);
         formData.append('action', 'delete');

         fetch('update_cart.php', {
            method: 'POST',
            body: formData
         })
            .then(res => res.json())
            .then(data => {
               if (data.success) {
                  alert('Item removed successfully.');
                  updateCartUI(); // Only update after confirmation
               } else {
                  alert('Failed to remove item: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error removing item:', err);
               alert('Something went wrong.');
            });
      }



   </script>

</body>

</html>