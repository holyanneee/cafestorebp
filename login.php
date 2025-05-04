<?php

@include 'config.php';

session_start();

if(isset($_POST['submit'])){

   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);
   $pass = md5($_POST['pass']);

   $sql = "SELECT * FROM `users` WHERE email = ? AND password = ?";
   $stmt = $conn->prepare($sql);
   $stmt->execute([$email, $pass]);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if($row){
      if($row['role'] == 'admin'){
         $_SESSION['admin_id'] = $row['id'];
         $_SESSION['admin_name'] = $row['name'];
         header('location:admin_page.php');
         exit;
      } elseif($row['role'] == 'user'){
         $_SESSION['user_id'] = $row['id'];
         $_SESSION['user_name'] = $row['name'];
         header('location:home.php');
         exit;
      }elseif($row['role'] == 'cashier'){
         $_SESSION['cashier_id'] = $row['id'];
         $_SESSION['cashier_name'] = $row['name'];
         header('location:cashier_page.php');
         exit;
      }elseif($row['role'] == 'barista'){
         $_SESSION['barista_id'] = $row['id'];
         $_SESSION['barista_name'] = $row['name'];
         header('location:barista_page.php');
         exit;
      } else {
         $message[] = 'Invalid user role!';
      }
   } else {
      $message[] = 'Incorrect email or password!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login</title>

   <!-- Font Awesome CDN link -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- Custom CSS file link -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>

<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>
   
<section class="form-container">
   <form action="" method="POST">
      <h3>Login Now</h3>
      <input type="email" name="email" class="box" placeholder="Enter your email" required>
      <input type="password" name="pass" class="box" placeholder="Enter your password" required>
      <input type="submit" value="Login Now" class="btn" name="submit">
      <p>Don't have an account? <a href="register.php">Register now</a></p>
   </form>
</section>

</body>
</html>
