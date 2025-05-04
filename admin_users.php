<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){

   $delete_id = $_GET['delete'];

   // Get the role of the user
   $check_role = $conn->prepare("SELECT role FROM `users` WHERE id = ?");
   $check_role->execute([$delete_id]);
   $user = $check_role->fetch(PDO::FETCH_ASSOC);

   if ($user && $user['role'] == 'admin') {
      echo "<script>alert('Action not permitted: Cannot delete an admin user.'); window.location.href='admin_roles.php';</script>";
      exit;
   }

   $delete_users = $conn->prepare("DELETE FROM `users` WHERE id = ?");
   $delete_users->execute([$delete_id]);
   header('location:admin_users.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>users</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/admin_style.css">

</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="user-accounts">

   <h1 class="title">user accounts</h1>

   
   <div class="box-container">

      <?php
         $select_users = $conn->prepare("SELECT * FROM `users`");
         $select_users->execute();
         while($fetch_users = $select_users->fetch(PDO::FETCH_ASSOC)){
      ?>
      <div class="box" style="<?php if($fetch_users['id'] == $admin_id){ echo 'display:none'; }; ?>">
         <img src="uploaded_img/<?= $fetch_users['image']; ?>" alt="">
         <p> user id : <span><?= $fetch_users['id']; ?></span></p>
         <p> username : <span><?= $fetch_users['name']; ?></span></p>
         <p> email : <span><?= $fetch_users['email']; ?></span></p>
         <p> user type : <span style=" color:<?php if($fetch_users['role'] == 'admin'){ echo 'orange'; }; ?>"><?= $fetch_users['role']; ?></span></p>
         <a href="admin_roles.php?delete=<?= $fetch_users['id']; ?>" onclick="return confirm('delete this user?');" class="delete-btn">delete</a>
         <?php if($fetch_users['id'] != $admin_id): ?>
           


<?php endif; ?>


</div>
      <?php
      }
      ?>
   </div>
</section>













<script src="js/script.js"></script>

</body>
</html>