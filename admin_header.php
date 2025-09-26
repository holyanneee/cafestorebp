<!DOCTYPE html>
<html lang="en">
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Panel</title>
   <link rel="stylesheet" href="css/admin_header.css">
   <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
   <style>
      /* Notification Dropdown Styles */
      .notifications {
         display: none;
         position: absolute;
         top: 60px;
         right: 10px;
         background-color: #fff;
         border: 1px solid #ccc;
         width: 300px;
         box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
         z-index: 1000;
      }

      .notifications h3 {
         font-size: 18px;
         margin: 0;
         padding: 10px;
         background-color: #f4f4f4;
         border-bottom: 1px solid #ccc;
      }

      .notifications ul {
         list-style: none;
         margin: 0;
         padding: 0;
      }

      .notifications ul li {
         padding: 10px;
         border-bottom: 1px solid #eee;
      }

      .notifications ul li:last-child {
         border-bottom: none;
      }

      .notifications.show {
         display: block;
      }

      .notification-count {
         position: absolute;
         top: 15px;
         right: 10px;
         background: red;
         color: white !important;
         border-radius: 50%;
         padding: 2px 6px;
         font-size: 12px !important;
         font-weight: bold;
      }
   </style>
   <script>
      document.addEventListener('DOMContentLoaded', function () {
         const userBtn = document.getElementById('user-btn');
         const profile = document.querySelector('.profile');
         const notificationBtn = document.getElementById('notification-btn');
         const notifications = document.querySelector('.notifications');

         userBtn.addEventListener('click', function () {
            profile.classList.toggle('active');
            notifications.classList.remove('show'); // Hide notifications if profile is shown
         });

         notificationBtn.addEventListener('click', function () {
            notifications.classList.toggle('show');
            profile.classList.remove('active'); // Hide profile if notifications are shown
         });

         // Close dropdowns when clicking outside
         document.addEventListener('click', function (event) {
            if (!userBtn.contains(event.target) && !profile.contains(event.target)) {
               profile.classList.remove('active');
            }
            if (!notificationBtn.contains(event.target) && !notifications.contains(event.target)) {
               notifications.classList.remove('show');
            }
         });
      });
   </script>
   </head>

   <body>

      <?php
      if (isset($message)) {
         foreach ($message as $message) {
            echo '
      <div class="message">
         <span>' . $message . '</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
         }
      }
      ?>

      <header class="header">
         <div class="flex">
            <!-- Left Side: Admin Panel -->
            <a href="admin_page.php" class="logo">Admin<span>Panel</span></a>

            <!-- Right Side: Profile Icon -->
            <div class="icons">
               <div id="user-btn" class="fas fa-user"></div>
               <div class="profile">
                  <?php
                  $select_profile = $conn->prepare("SELECT * FROM users WHERE id = ?");
                  $select_profile->execute([$admin_id]);
                  $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
                  ?>
                  <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
                  <p><?= $fetch_profile['name']; ?></p>
                  <a href="admin_update_profile.php" class="btn">Update Profile</a>
                  <a href="logout.php" class="delete-btn">Logout</a>
               </div>
               <?php

               $select_ingredients = $conn->prepare("SELECT * FROM ingredients WHERE unit IN ('grams', 'pieces')");
               $select_ingredients->execute();
               $fetch_ingredients = $select_ingredients->fetchAll(PDO::FETCH_ASSOC);

                  $select_products = $conn->prepare("SELECT * FROM products WHERE stock < 5 AND type = 'online'");
               $select_products->execute();
               $fetch_products = $select_products->fetchAll(PDO::FETCH_ASSOC);

               ?>
               <div id="notification-btn" class="fas fa-bell"></div>
               <?php
               $low_stock_count = 0;
               foreach ($fetch_ingredients as $notification) {
                  if (
                     ($notification['unit'] == 'grams' && $notification['stock'] < 500) ||
                     ($notification['unit'] == 'pieces' && $notification['stock'] < 5)
                  ) {
                     $low_stock_count++;
                  }
               }
               $low_stock_count = count($fetch_products) + $low_stock_count;
               if ($low_stock_count > 0) {
                  echo "<span class=\"notification-count\">{$low_stock_count}</span>";
               }
               ?>
               <div class="notifications">
                  <h3>Notifications</h3>
                  <ul>
                     <?php foreach ($fetch_ingredients as $notification): ?>
                        <?php

                        if ($notification['unit'] == 'grams' && $notification['stock'] < 500) {
                           echo '
                               <li>
                              <strong>' . htmlspecialchars($notification['name']) . '</strong> is running low (' . htmlspecialchars($notification['stock'] . ' ' . $notification['unit']) . ' left).
                               </li>';
                        } elseif ($notification['unit'] == 'pieces' && $notification['stock'] < 5) {
                           echo '
                               <li>
                              <strong>' . htmlspecialchars($notification['name']) . '</strong> is running low (' . htmlspecialchars($notification['stock'] . ' ' . $notification['unit']) . ' left).
                               </li>
                               ';
                        }
                        ?>


                     <?php endforeach; ?>
                     <?php if (empty($fetch_ingredients)): ?>
                        <li>No new notifications.</li>
                     <?php endif; ?>
                  </ul>
               </div>
            </div>
         </div>
      </header>



      <?php include 'admin_sidebar.php'; ?>


      <script src="js/admin_profile.js"></script>

   </body>

</html>