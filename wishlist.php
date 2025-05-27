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
   <title>Wishlist</title>

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
</head>

<body>

   <?php include 'header.php'; ?>

   <section class="wishlist-container">
      <div class="container">
         <div class="row mt-5">

            <?php
            $total = 0;
            // Fetch wishlist items from the database
            $select_wishlist = $conn->prepare("SELECT wishlist.*, products.name, products.price, products.details, products.image FROM `wishlist` 
                  JOIN `products` ON wishlist.product_id = products.id 
                  WHERE wishlist.user_id = ? AND wishlist.type = ? ORDER BY wishlist.id DESC");
            $select_wishlist->execute([$user_id, $type]);
            $wishlist_items = $select_wishlist->fetchAll(PDO::FETCH_ASSOC);
            $count_cart_items = $select_wishlist->rowCount();
            ?>
            <div class="col-lg-7">
               <h5 class="mb-3 h3"><a href="shop.php" class="text-body"><i
                        class="fas fa-long-arrow-alt-left me-2"></i>Continue
                     shopping</a></h5>
               <hr>

               <div class="d-flex justify-content-between align-items-center mb-4">
                  <div>
                     <p class="mb-1 h4">Wishlist</p>

                  </div>
                  <div>
                     <p class="mb-0 h4">You have
                        <?= $count_cart_items ?> items in your wishlist
                     </p>
                  </div>
               </div>
               <?php if ($count_cart_items > 0): ?>
                  <?php foreach ($wishlist_items as $item): ?>
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
                                    <h4>₱<?= $item['price'] ?></h4>
                                 </div>
                              </div>
                              <div class="d-flex flex-row align-items-center">

                                 <div class="d-flex justify-content-between align-items-center">
                                    <a href="#!" class="text-muted remove-btn" data-id="<?= $item['id'] ?>">Remove</a>
                                    <span class="mx-2 text-center">|</span>
                                    <a href="#!" class="cart-btn" data-id="<?= $item['id'] ?>">
                                       Add to Cart
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
                        <h3 class="text-center">Your wishlist is empty</h3>
                     </div>
                  </div>
               <?php endif; ?>

            </div>
            <div class="col-lg-5">
               <div class="card bg-success text-white rounded-3 mt-5">
                  <div class="card-body">
                     <button type="button" 
                        class="btn btn-danger btn-block btn-lg" id="">
                        <span class="text-white">
                           Remoive All
                           <i class="fas fa-trash ms-2 text-white"></i>
                        </span>
                     </button>
                     <button type="button"  class="btn btn-info btn-block btn-lg" id="add-all-btn">
                        <span class="text-white">
                           Add to all items to cart
                           <i class="fas fa-cart-shopping ms-2 text-white"></i>
                        </span>
                     </button>

                  </div>
               </div>

            </div>

         </div>
      </div>

   </section>








   <?php include 'footer.php'; ?>

   <script src="js/script.js"></script>
   <script>
      document.addEventListener('DOMContentLoaded', function () {
         document.querySelectorAll('.remove-btn').forEach(element => {
            element.addEventListener('click', function () {
               const id = this.dataset.id;
               removeFromWishlist(id);
            });
         });

         document.querySelectorAll('.cart-btn').forEach(element => {
            element.addEventListener('click', function () {
               const id = this.dataset.id;
               addToCart(id);
            });
         });

         document.getElementById('remove-all-btn')?.addEventListener('click', function () {
            removeAllItemFromWishlist();
         });

         document.getElementById('add-all-btn')?.addEventListener('click', function () {
            addAllItemFromWishlist();
         });
      });

      function removeFromWishlist(id) {
         const formData = new FormData();
         formData.append('wishlist_id', id);
         formData.append('action', 'delete');

         fetch('update_wishlist.php', {
            method: 'POST',
            body: formData
         })
            .then(res => res.json())
            .then(data => {
               if (data.success) {
                  alert('Item removed successfully.');
                  location.reload();
               } else {
                  alert('Failed to remove item: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error removing item:', err);
               alert('Something went wrong.');
            });
      }

      function removeAllItemFromWishlist() {
         const formData = new FormData();
         formData.append('action', 'delete_all');

         fetch('update_wishlist.php', {
            method: 'POST',
            body: formData
         })
            .then(res => res.json())
            .then(data => {
               if (data.success) {
                  alert('All wishlist items removed.');
                  location.reload();
               } else {
                  alert('Failed to remove all: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error:', err);
               alert('Something went wrong.');
            });
      }

      function addToCart(id) {
         const formData = new FormData();
         formData.append('wishlist_id', id);
         formData.append('action', 'store');

         fetch('update_wishlist.php', {
            method: 'POST',
            body: formData
         })
            .then(res => res.json())
            .then(data => {
               if (data.success) {
                  alert('Item added to cart.');
                  location.reload();
               } else {
                  alert('Failed to add to cart: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error:', err);
               alert('Something went wrong.');
            });
      }

      function addAllItemFromWishlist() {
         const formData = new FormData();
         formData.append('action', 'store_all');

         fetch('update_wishlist.php', {
            method: 'POST',
            body: formData
         })
            .then(res => res.json())
            .then(data => {
               if (data.success) {
                  alert('All wishlist items added to cart.');
                  location.reload();
               } else {
                  alert('Failed to add all to cart: ' + data.message);
               }
            })
            .catch(err => {
               console.error('Error:', err);
               alert('Something went wrong.');
            });
      }
   </script>

</body>

</html>