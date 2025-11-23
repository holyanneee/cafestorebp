<?php
include 'config.php';
require 'services/mail.php';
session_start();

$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

   // Sanitize Inputs
   $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
   $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
   $password = $_POST['password'] ?? '';
   $confirm_password = $_POST['confirm_password'] ?? '';
   $role = isset($_POST['is_admin']) ? 'admin' : 'user';

   // ============================================
   // ✅ IMAGE UPLOAD FIX
   // ============================================

   $image_name = $_FILES['image']['name'] ?? '';
   $image_size = $_FILES['image']['size'] ?? 0;
   $image_tmp = $_FILES['image']['tmp_name'] ?? '';

   $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

   $uploaded_image = null; // default (if no file is uploaded)

   if (!empty($image_name) && is_uploaded_file($image_tmp)) {

      // Extract file extension
      $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

      if (!in_array($ext, $allowed_ext)) {
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid image format. Only JPG, PNG, WEBP allowed.'];
         header("Location: register.php");
         exit;
      }

      if ($image_size > 2 * 1024 * 1024) { // 2MB limit
         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Image size is too large! Limit: 2MB'];
         header("Location: register.php");
         exit;
      }

      // Generate unique filename
      $uploaded_image = uniqid('IMG_', true) . "." . $ext;

      // Ensure upload directory exists
      $upload_dir = 'uploaded_img/';
      if (!is_dir($upload_dir)) {
         mkdir($upload_dir, 0777, true);
      }

      $image_path = $upload_dir . $uploaded_image;
   }



   // Check email existence
   $select = $conn->prepare("SELECT 1 FROM `users` WHERE email = ?");
   $select->execute([$email]);

   if ($select->rowCount() > 0) {
      $_SESSION['alert'] = ['type' => 'error', 'message' => 'Email already exists!'];
      header("Location: register.php");
      exit;
   }

   // Password match validation
   if ($password !== $confirm_password) {
      $_SESSION['alert'] = ['type' => 'error', 'message' => 'Passwords do not match!'];
      header("Location: register.php");
      exit;
   }

   // Hash password
   $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

   // Insert user
   $insert = $conn->prepare("
        INSERT INTO `users` (name, email, password, image, role) 
        VALUES (?, ?, ?, ?, ?)
    ");
   $insert->execute([$name, $email, $hashedPassword, $uploaded_image, $role]);

   // Move uploaded file only after DB insert
   if ($uploaded_image && is_uploaded_file($image_tmp)) {
      move_uploaded_file($image_tmp, $image_path);
   }

   // Generate email verification code
   $verification_code = bin2hex(random_bytes(6));

   // Send verification email
   $message = sendVerificationEmail($email, $verification_code);

   if ($message['status'] === 'error') {
      $_SESSION['alert'] = ['type' => 'error', 'message' => $message['message']];
      header("Location: register.php");
      exit;
   }

   // Save code to session
   $_SESSION['verification_code'] = $verification_code;

   $_SESSION['alert'] = [
      'type' => 'success',
      'message' => 'Registration successful! Please verify your email.'
   ];

   header('Location: verify.php?email=' . urlencode($email));
   exit;
}
?>





<!DOCTYPE html>
<html lang="en" class="h-full bg-white">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register | Kape Milagrosa
   </title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

   <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="h-full">

   <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
         <!-- <img src="images/kape_milag.jpg" alt="kape_milagrosa_logo" class="mx-auto h-20 w-auto rounded-full" /> -->
         <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">
            Create a new account
         </h2>
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
         <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="submit" value="1">
            <div>
               <label for="name" class="block text-sm/6 font-medium text-gray-900">Name</label>
               <div class="mt-2">
                  <input id="name" type="text" name="name" required autocomplete="name"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>
            </div>
            <div>
               <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
               <div class="mt-2">
                  <input id="email" type="email" name="email" required autocomplete="email"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>
            </div>

            <div>
               <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
               <div class="mt-2">
                  <input id="password" type="password" name="password" required autocomplete="current-password"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>
            </div>

            <div>
               <label for="confirm_password" class="block text-sm/6 font-medium text-gray-900">Confirm Password</label>
               <div class="mt-2">
                  <input id="confirm_password" type="password" name="confirm_password" required
                     autocomplete="current-password"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>

            </div>

            <div>
               <label for="image" class="block text-sm/6 font-medium text-gray-900">Upload Image</label>
               <div class="mt-2">
                  <input id="image" type="file" name="image" accept="image/jpg, image/jpeg, image/png"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />

               </div>

               <div class="flex flex-col gap-3 mt-6">
                  <button type="submit"
                     class="flex w-full justify-center rounded-md bg-color px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs  focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                     Register
                  </button>
               </div>
         </form>

         <p class="mt-10 text-center text-sm/6 text-gray-500">
            Already have an account?
            <a href="login.php" class="font-semibold text-color hover:border-b-2 hover:border-color">
               Sign in
            </a>
         </p>
      </div>
   </div>

   <!-- sweet alert -->
   <script src=" https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
   <?php if (!empty($alert)): ?>
      <script>
         document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
               icon: '<?= $alert['type'] ?>',
               title: '<?= $alert['message'] ?>',
               showConfirmButton: false,
               timer: 1500
            });
         });
      </script>
   <?php endif; ?>

</body>

</html>