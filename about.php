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
      <section class="mt-30 mb-20">
         <section>
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
               <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-center md:gap-8">
                  <div>
                     <div class="max-w-prose md:max-w-none">
                        <h2 class="text-2xl font-semibold text-color sm:text-3xl">
                           Kape Milagrosa
                        </h2>

                        <p class="mt-4 text-gray-700">
                           Founded on February 18, 2024 in Lamot 2, Calauan, Laguna, Kape Milagrosa was created to bring
                           the community together over good coffee, refreshing drinks, and delicious treats.
                           <br> <br>
                           We serve a variety of favorites — from hot & iced coffee, milk tea, fruit tea, fruit milk,
                           fruit soda, frappes, to freshly baked pastries and more. Whether it’s for a quick break or a
                           chill food trip, there’s always something here for you.
                           <br> <br>
                           At Kape Milagrosa, every cup is crafted with care, and every visit feels like home. Tara na,
                           kape o foodtrip tayo! ☕✨
                        </p>
                     </div>
                  </div>

                  <div>
                     <img src="images/kape milagrosa.jpg" class="rounded" alt="Kape Milagrosa" />
                  </div>
               </div>
            </div>

            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 lg:px-8">
               <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:items-center md:gap-8">
                  <div>
                     <img src="/images/kape milagrosa.jpg" class="rounded" alt="" />
                  </div>
                  <div>
                     <div class="max-w-prose md:max-w-none">
                        <h2 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                           Anak ng Birhen
                        </h2>

                        <p class="mt-4 text-gray-700">
                           Anak ng Birhen ng Medalya Milagrosa is dedicated to Our Lady of the Miraculous Medal, with
                           the mission of spreading the Catholic faith and nurturing devotion to the Blessed Virgin
                           Mary.
                           <br> <br>
                           Aside from faith-centered content, we also offer religious items such as medals, rosaries,
                           novenas, and other devotional articles — to help strengthen personal prayer and spiritual
                           life.
                           <br> <br>
                           Our goal is to build a community rooted in faith, hope, and love, while providing meaningful
                           items that bring people closer to God through the guidance and intercession of the Blessed
                           Mother.
                        </p>
                     </div>
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