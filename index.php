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
   <title>Home</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">

   <style>
      .category-buttons {
         display: flex;
         flex-wrap: wrap;
         gap: 10px;
         justify-content: center;
         margin-top: 20px;
      }

      .category-btn {
         text-decoration: none;
         padding: 12px 20px;
         border: 2px solid darkgreen;
         border-radius: 15px;
         font-weight: bold;
         font-size: 16px;
         color: black;
         transition: all 0.3s ease-in-out;
      }


      .category-btn:hover,
      .category-btn.active {
         background: darkgreen;
         color: white;
      }

      .home .content {
         color: white;
         text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8);
      }

      .home .content span,
      .home .content h3,
      .home .content p {
         color: white;
         text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.8);
      }

      .container {
         display: flex;
         flex-wrap: wrap;
         gap: 20px;
         justify-content: center;
         padding: 20px;
      }

      .card {
         width: 30rem;
         border: 1px solid #ccc;
         border-radius: 8px;
         background-color: white;
         overflow: hidden;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      .card img {
         width: 100%;
         height: 250px;
         object-fit: cover;
      }

      .card-body {
         padding: 16px;
         display: flex;
         justify-content: space-between;
      }

      .card-title {
         font-size: 2rem;
         margin-bottom: 8px;
      }

      .card-text {
         font-size: 1.5rem;
         margin-bottom: 16px;
         color: #555;
      }

      .card-text.details {
         font-size: 1.4rem;
         color: #777;
         max-height: 100px;
         overflow: hidden;
         text-overflow: ellipsis;
         margin-bottom: 0;
      }

      .card-footer {
         display: flex;
         justify-content: space-between;
         padding: 16px;
      }

      .btn-cart {
         display: inline-block !important;
         padding: 10px 20px !important;
         width: 100% !important;
         background-color: darkgreen !important;
         color: white !important;
         text-decoration: none !important;
         font-size: 1.5rem !important;
         text-align: center !important;
         border-radius: 5px !important;
         font-weight: bold !important;
         transition: background-color 0.3s ease-in-out !important;
      }

      a.btn-cart i {
         color: white !important;
      }



      .btn-cart:hover {
         background-color: green !important;
      }

      .btn-favourite {
         display: inline-block !important;
         padding: 5px 10px !important;
         background-color: #f8f9fa !important;
         color: #dc3545 !important;
         text-decoration: none !important;
         text-align: center !important;
         font-size: 1.5rem !important;
         border: 2px solid #dc3545 !important;

         border-radius: 5px !important;
         font-weight: bold !important;
         transition: background-color 0.3s ease-in-out !important;
      }

      a.btn-favourite i {
         color: #dc3545 !important;
      }

      .btn-favourite:hover {
         background-color: #dc3545 !important;
         color: white !important;
      }

      a.btn-favourite:hover i {
         color: white !important;
      }
   </style>
</head>

<body style="flex: 1;">

   <?php include 'header.php'; ?>
   <main style="flex: 1;">
      <div class="home-bg">
         <section class="home">
            <?php if ($current_store === 'kape_milagrosa') { ?>
               <div class="content" style="color: white;">
                  <span style="color: white;">Enjoy Every Sip</span>
                  <h3 style="color: white;">Discover the Best Beverages for You</h3>
                  <p style="color: white;">Explore our wide range of drinks, from frappes to milk teas, fruit sodas, and
                     more.
                  </p>
                  <a href="about.php" class="btn">about us</a>
               </div>
            <?php } elseif ($current_store === 'anak_ng_birhen') { ?>
               <div class="content" style="color: white;">
                  <span style="color: white;">Faith in Every Piece</span>
                  <h3 style="color: white;">Discover Meaningful Religious Items</h3>
                  <p style="color: white;">Explore our collection of religious keepsakes, from rosaries to prayer pockets,
                     statues, and more.</p>
                  <a href="about.php" class="btn">about us</a>
               </div>
            <?php } ?>
         </section>
      </div>


      <section class="home-category">
         <h1 class="title">Shop by Category</h1>
         <?php if ($current_store === 'kape_milagrosa') { ?>
            <div class="category-buttons">
               <a href="index.php?category=Frappe"
                  class="category-btn <?= ($category == 'Frappe') ? 'active' : '' ?>">Frappe</a>
               <a href="index.php?category=Fruit Soda"
                  class="category-btn <?= ($category == 'Fruit Soda') ? 'active' : '' ?>">Fruit Soda</a>
               <a href="index.php?category=Frappe Extreme"
                  class="category-btn <?= ($category == 'Frappe Extreme') ? 'active' : '' ?>">Frappe Extreme</a>
               <a href="index.php?category=Milk Tea"
                  class="category-btn <?= ($category == 'Milk Tea') ? 'active' : '' ?>">Milk
                  Tea</a>
               <a href="index.php?category=Fruit Tea"
                  class="category-btn <?= ($category == 'Fruit Tea') ? 'active' : '' ?>">Fruit Tea</a>
               <a href="index.php?category=Fruit Milk"
                  class="category-btn <?= ($category == 'Fruit Milk') ? 'active' : '' ?>">Fruit Milk</a>
               <a href="index.php?category=Espresso"
                  class="category-btn <?= ($category == 'Espresso') ? 'active' : '' ?>">Espresso</a>
               <a href="index.php?category=Hot Non-Coffee"
                  class="category-btn <?= ($category == 'Hot Non-Coffee') ? 'active' : '' ?>">Hot Non-Coffee</a>
               <a href="index.php?category=Iced Non-Coffee"
                  class="category-btn <?= ($category == 'Iced Non-Coffee') ? 'active' : '' ?>">Iced Non-Coffee</a>
               <a href="index.php?category=Meal" class="category-btn <?= ($category == 'Meal') ? 'active' : '' ?>">Meal</a>
               <a href="index.php?category=Snacks"
                  class="category-btn <?= ($category == 'Snacks') ? 'active' : '' ?>">Snacks</a>
               <a href="index.php" class="category-btn">All</a>
            </div>
         <?php } elseif ($current_store === 'anak_ng_birhen') { ?>
            <div class="category-buttons">
               <a href="index.php?category=Chibi Religious Items" class="category-btn">Chibi Religious Items</a>
               <a href="index.php?category=Angels" class="category-btn">Angels</a>
               <a href="index.php?category=Cross" class="category-btn">Cross</a>
               <a href="index.php?category=Prayer Pocket" class="category-btn">Prayer Pocket</a>
               <a href="index.php?category=Rosary" class="category-btn">Rosary</a>
               <a href="index.php?category=Ref Magnet" class="category-btn">Ref Magnet</a>
               <a href="index.php?category=Keychain" class="category-btn">Keychain</a>
               <a href="index.php?category=Scapular" class="category-btn">Scapular</a>
               <a href="index.php?category=Statues" class="category-btn">Statues</a>
               <a href="index.php" class="category-btn">All</a>
            </div>
         <?php } ?>
      </section>

      <section class="products">
         <h1 class="title">Latest products</h1>
         <div class="container">
            <?php
            if ($products) {
               foreach ($products as $product) {
                  ?>
                  <div class="card">
                     <img src="uploaded_img/<?= $product['image']; ?>" alt="<?= $product['name']; ?>">
                     <div class="card-body">
                        <div>
                           <h5 class="card-title"><?= $product['name']; ?></h5>
                           <p class="card-text">₱<?= number_format($product['price'], 2); ?></p>
                           <p class="card-text details">
                              <?= $product['details']; ?>
                           </p>
                        </div>
                        <div>
                              <a href="index.php?action=add_to_wishlist&product_id=<?= $product['id']; ?>" class="btn-favourite">
                                 <i class="fas fa-heart"></i>
                              </a>
                           </a>
                        </div>
                     </div>
                     <div class="card-footer">
                        <a href="index.php?action=add_to_cart&product_id=<?= $product['id']; ?>" class="btn-cart">
                           <i class="fas fa-shopping-cart"></i> Add to Cart
                        </a>
                     </div>
                  </div>
                  <?php
               }
            } else {
               echo '<p class="empty">No active products available at the moment!</p>';
            }
            ?>
         </div>
      </section>

   </main>

   <?php include 'footer.php'; ?>
   <script src="js/script.js"></script>
</body>

</html>