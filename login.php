<?php

@include 'config.php';

session_start();

$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {

   // Sanitize input safely
   $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
   $password = $_POST['password'] ?? '';

   // Fetch the user by email only
   $stmt = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
   $stmt->execute([$email]);
   $row = $stmt->fetch(PDO::FETCH_ASSOC);

   if (!$row || !password_verify($password, $row['password'])) {
      // If the user doesn't exist or password mismatch
      $alert = [
         'type' => 'error',
         'message' => 'Incorrect email or password!'
      ];
   } else {
      // Role → Redirect map
      $roleRedirects = [
         'admin' => 'admin_page.php',
         'user' => 'index.php',
         'cashier' => 'cashier_page.php',
         'barista' => 'barista_page.php',
      ];

      // Store session with a consistent pattern
      $role = strtolower($row['role']);
      if (isset($roleRedirects[$role])) {
         $_SESSION[$role . '_id'] = $row['id'];
         $_SESSION[$role . '_name'] = $row['name'];
         header('Location: ' . $roleRedirects[$role]);
         exit;
      } else {
         $alert = [
            'type' => 'error',
            'message' => 'Unknown user role!'
         ];
      }
   }
}


?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-white">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Login | Kape Milagrosa
   </title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

   <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="h-full">

   <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
         <img src="images/kape_milag.jpg" alt="kape_milagrosa_logo" class="mx-auto h-20 w-auto rounded-full" />
         <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Sign in to your account</h2>
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
         <form action="" method="POST" class="space-y-6">
            <input type="hidden" name="submit" value="1">
            <div>
               <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
               <div class="mt-2">
                  <input id="email" type="email" name="email" required autocomplete="email"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>
            </div>

            <div>
               <div class="flex items-center justify-between">
                  <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
                  <!-- <div class="text-sm">
                     <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
                  </div> -->
               </div>
               <div class="mt-2">
                  <input id="password" type="password" name="password" required autocomplete="current-password"
                     class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
               </div>
            </div>

            <div class="flex flex-col gap-3">
               <button type="submit"
                  class="flex w-full justify-center rounded-md bg-color px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs  focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign
                  in</button>
               <a href="index.php"
                  class="flex w-full justify-center rounded-md bg-gray-100 px-3 py-1.5 text-sm/6 font-semibold text-gray-900 shadow-xs  focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Back
                  to Home</a>
            </div>
         </form>

         <p class="mt-10 text-center text-sm/6 text-gray-500">
            Don't have an account?
            <a href="register.php" class="font-semibold text-color hover:border-b-2 hover:border-color">
               Sign up
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