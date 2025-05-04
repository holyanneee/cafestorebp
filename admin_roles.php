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
   header('location:admin_roles.php');
}


if(isset($_GET['make_barista'])){

   $barista_id = $_GET['make_barista'];

   $check_role = $conn->prepare("SELECT role FROM `users` WHERE id = ?");
   $check_role->execute([$barista_id]);
   $user = $check_role->fetch(PDO::FETCH_ASSOC);

   if ($user && $user['role'] == 'admin') {
      echo "<script>alert('Action not permitted: Cannot change role of an admin.'); window.location.href='admin_roles.php';</script>";
      exit;
   }

   $update_role = $conn->prepare("UPDATE `users` SET role = 'barista' WHERE id = ?");
   $update_role->execute([$barista_id]);
   header('location:admin_roles.php');
}


if(isset($_GET['make_cashier'])){

   $cashier_id = $_GET['make_cashier'];

   $check_role = $conn->prepare("SELECT role FROM `users` WHERE id = ?");
   $check_role->execute([$cashier_id]);
   $user = $check_role->fetch(PDO::FETCH_ASSOC);

   if ($user && $user['role'] == 'admin') {
      echo "<script>alert('Action not permitted: Cannot change role of an admin.'); window.location.href='admin_roles.php';</script>";
      exit;
   }

   $update_role = $conn->prepare("UPDATE `users` SET role = 'cashier' WHERE id = ?");
   $update_role->execute([$cashier_id]);
   header('location:admin_roles.php');
}



if(isset($_GET['fire_barista'])){

   $user_id = $_GET['fire_barista'];

   $check_role = $conn->prepare("SELECT role FROM `users` WHERE id = ?");
   $check_role->execute([$user_id]);
   $user = $check_role->fetch(PDO::FETCH_ASSOC);

   if ($user && $user['role'] == 'admin') {
      echo "<script>alert('Action not permitted: Cannot fire an admin.'); window.location.href='admin_roles.php';</script>";
      exit;
   }

   $update_role = $conn->prepare("UPDATE `users` SET role = 'user' WHERE id = ?");
   $update_role->execute([$user_id]);
   header('location:admin_roles.php');
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

   <h1 class="title">User Roles</h1>

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
            <?php if($fetch_users['id'] != $admin_id): ?>
   <?php if($fetch_users['role'] == 'user'): ?>
      <!-- Dropdown for choosing role -->
      <div class="role-dropdown">
   <select onchange="confirmRoleChange(this)">
      <option value="">Choose Role</option>
      <option value="admin_roles.php?make_barista=<?= $fetch_users['id']; ?>">Make Barista</option>
      <option value="admin_roles.php?make_cashier=<?= $fetch_users['id']; ?>">Make Cashier</option>
   </select>
</div>
   <?php else: ?>
      <a href="admin_roles.php?fire_barista=<?= $fetch_users['id']; ?>" onclick="return confirm('Fire this user back to regular user?');" class="option-btn fire">Fire</a>
   <?php endif; ?>
<?php endif; ?>

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

<style>
    .option-btn {
        display: inline-block;
        margin-top: 1rem;
        padding: .8rem 2rem;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        border-radius: .5rem;
        cursor: pointer;
        transition: .3s;
        text-decoration: none;
        
    }
    .option-btn:hover {
        background-color: #45a049;
    }

    .option-btn.fire {
        background-color: #e74c3c;
    }
    .option-btn.fire:hover {
        background-color: #c0392b;
    }

    /* Additional style for the dropdown */
    .role-dropdown select {
        display: inline-block;
        margin-top: 1rem;
        padding: 1.3rem 2rem; /* Increase top and bottom padding to adjust the height */
        background-color: #4CAF50;
        color: white;
        text-align: center;
        border-radius: .5rem;
        cursor: pointer;
        transition: .3s;
        text-decoration: none;
        border: none;
        appearance: none;
        width: 100%;
        font-size: 1.3rem; /* Increase font size */

    }

    .role-dropdown select:hover {
        background-color: #45a049;
    }

    /* Style for the dropdown options */
    .role-dropdown select option {
        background-color: #f0f0f0; /* Light gray background */
        color: #333; /* Dark text color for contrast */
    }

    .role-dropdown select option:hover {
        background-color: #ddd; /* Slightly darker gray on hover */
    }
</style>

</style>

<script>
   function confirmRoleChange(selectElement) {
      const selectedValue = selectElement.value;
      
      if (selectedValue) {
         const isConfirmed = confirm("Are you sure you want to change this user's role?");
         if (isConfirmed) {
            window.location.href = selectedValue;
         } else {
            selectElement.selectedIndex = 0; // Reset the dropdown to the default option
         }
      }
   }
</script>