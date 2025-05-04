<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Admin Panel</title>
   <link rel="stylesheet" href="css/admin_header.css">
   <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
         <a href="barista_page.php" class="logo">Barista<span>Panel</span></a>

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
         </div>
      </div>
   </header>



   <?php include 'barista_sidebar.php'; ?>


   <script src="js/admin_profile.js"></script>

</body>

</html>