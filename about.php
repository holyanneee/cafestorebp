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
   <title>About | Kape Milagrosa
   </title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

   <link rel="stylesheet" href="css/new_style.css">
</head>

<body>

   <?php include 'header.php'; ?>

   <main>
      <section class="mt-20">
         <section>
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
               <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-center md:gap-8">
                  <div>
                     <div class="max-w-prose md:max-w-none">
                        <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                           Kape Milagrosa
                        </h2>

                        <p class="mt-4 text-gray-700">
                           Hot & Iced Coffee, Milk Tea, Fruit Tea, Fruit Milk, Fruit Soda, Frappe, Pastries, and more!
                        </p>
                     </div>
                  </div>

                  <div>
                     <img
                        src="https://images.unsplash.com/photo-1731690415686-e68f78e2b5bd?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        class="rounded" alt="" />
                  </div>
               </div>
            </div>

            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
               <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-center md:gap-8">
                  <div>
                     <div class="max-w-prose md:max-w-none">
                        <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                           Lorem ipsum dolor sit amet consectetur adipisicing elit.
                        </h2>

                        <p class="mt-4 text-gray-700">
                           Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur doloremque saepe
                           architecto maiores repudiandae amet perferendis repellendus, reprehenderit voluptas
                           sequi.
                        </p>
                     </div>
                  </div>

                  <div>
                     <img
                        src="https://images.unsplash.com/photo-1731690415686-e68f78e2b5bd?q=80&w=2670&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                        class="rounded" alt="" />
                  </div>
               </div>
            </div>



         </section>

      </section>


   </main>

   <?php include 'footer.php'; ?>

   <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
</body>

</html>