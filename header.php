<?php
ob_start(); // Start output buffering to prevent premature output

require_once 'enums/OrderStatusEnum.php';

use enums\OrderStatusEnum;

$completedStatus = OrderStatusEnum::Completed;

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}



// get user id
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;


// get user info and 
if ($user_id) {
  $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
  $select_user->execute([$user_id]);
  $user = $select_user->fetch(PDO::FETCH_ASSOC);
}

// check the url if not on index.php
$current_page = basename($_SERVER['PHP_SELF']);


ob_end_flush(); // Flush the output buffer and turn off output buffering

?>

<header class="absolute inset-x-0 top-0 z-50 <?= $current_page != 'index.php' ? 'bg-color' : '' ?>">
  <nav aria-label="Global" class="flex items-center justify-between px-2 py-3 lg:px-15">
    <div class="flex lg:flex-1">
      <a href="index.php" class="-m-1.5 p-1.5 flex items-center gap-3">
        <span class="sr-only">Kape Milagrosa</span>
        <img src="images/kape_milag.jpg" alt="" class="h-15 w-auto rounded-full <?= $current_page != 'index.php' ? 'p-1 bg-white' : '' ?> " />
        <span class="text-white text-lg font-bold">
          Kape Milagrosa
        </span>
      </a>
    </div>
    <div class="flex lg:hidden">
      <button type="button" command="show-modal" commandfor="mobile-menu"
        class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-200">
        <span class="sr-only">Open main menu</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
          aria-hidden="true" class="size-6">
          <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </button>
    </div>
    <div class="hidden lg:flex lg:gap-x-12 nav">
      <a href="index.php" class="text-sm/6 font-semibold text-white">Home</a>
      <a href="products.php" class="text-sm/6 font-semibold text-white">Products</a>

      <a href="about.php" class="text-sm/6 font-semibold text-white">About Us</a>
      <a href="#contact-us" class="text-sm/6 font-semibold text-white">Contact Us</a>
    </div>
    <div class="hidden lg:flex lg:flex-1 lg:justify-end">
      <?php if ($user_id && $user): ?>
        <div class="flex items-center gap-4">
          <a href="wishlist.php" class="text-sm/6 font-semibold text-white relative">
            <span class="sr-only">Wishlist</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
              aria-hidden="true" class="size-6">
              <path
                d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <?php
            // get number of items in wishlist
            $select_wishlist_count = $conn->prepare("SELECT COUNT(*) FROM `wishlist` WHERE user_id = ?");
            $select_wishlist_count->execute([$user_id]);
            $wishlist_count = $select_wishlist_count->fetchColumn();
            ?>
            <?php if ($wishlist_count > 0): ?>
              <span
                class="absolute -top-2 -right-2 inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                <?= $wishlist_count ?>
              </span>
            <?php endif; ?>
            
          </a>
          <a href="cart.php" class="text-sm/6 font-semibold text-white relative">
            <span class="sr-only">Cart</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
              aria-hidden="true" class="size-6">
              <path d="M2.25 2.25h1.386c.51 0 .955.343 1.087.835l1.513 6.435M7.5 14.25a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm10.5 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM7.5 14.25h10.5m-10.5 0L6.16 5.59m11.34 8.66L19.5 6M6.16 5.59l-.31-1.
91a1.125 1.125 0 0 0-1.087-.835H3.375M19.5 6l-2.4-2.4a1.125 1.125 0 0 0-1.087-.835h-7.26" stroke-linecap="round"
                stroke-linejoin="round" />
            </svg>
            <?php
            // get number of items in cart
            $select_cart_count = $conn->prepare("SELECT COUNT(*) FROM `cart` WHERE user_id = ?");
            $select_cart_count->execute([$user_id]);
            $cart_count = $select_cart_count->fetchColumn();
            ?>
            <?php if ($cart_count > 0): ?>
              <span
                class="absolute -top-2 -right-2 inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                <?= $cart_count ?>
              </span>
            <?php endif; ?>
          </a>
          <a href="orders.php" class="text-sm/6 font-semibold text-white relative">
            <span class="sr-only">Orders</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
              aria-hidden="true" class="size-6">
              <path
                d="M3 3v1.5M3 21v-6m0 0l2.5-2.5m-2.5 2.5L5.5 9M21 3v1.5M21 21v-6m0 0l-2.5-2.5m2.5 2.5L18.5 9M3 8.25h18M3 12h18m-9 8.25h9"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <?php
            // get number of orders
            $select_orders_count = $conn->prepare("SELECT COUNT(*) FROM `orders` WHERE user_id = ?");
            $select_orders_count->execute([$user_id]);
            $orders_count = $select_orders_count->fetchColumn();
            ?>
            <?php if ($orders_count > 0): ?>
              <span
                class="absolute -top-2 -right-2 inline-flex items-center justify-center rounded-full bg-red-600 p-1 text-xs font-bold leading-none text-white">
                <?= $orders_count ?>
              </span>
            <?php endif; ?>
          </a>
          <div class="relative">
            <button type="button" class="flex items-center gap-2 text-sm/6 font-semibold text-white" id="user-menu-button"
              aria-expanded="false" aria-haspopup="true">
              <span class="sr-only">Open user menu</span>
              <span>

                <!-- icon -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                  aria-hidden="true" class="size-6">
                  <path
                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a8.25 8.25 0 0 1 14.998 0 .75.75 0 0 1-.666 1.132h-13.66a.75.75 0 0 1-.672-1.132Z"
                    stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                aria-hidden="true" class="size-4">
                <path d="m8.25 10.5 3.75 3.75 3.75-3.75" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
            <div
              class="absolute right-0 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none hidden"
              role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button" tabindex="-1" id="user-menu">
              <!-- Active: "bg-gray-100", Not Active: "" -->
              <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100
              " role="menuitem" tabindex="-1" id="user-menu-item-0">Your Profile</a>
              <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"
                tabindex="-1" id="user-menu-item-2">Sign out</a>
            </div>
          </div>
        </div>
        <script>
          // Toggle user menu
          document.getElementById('user-menu-button').addEventListener('click', function () {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
          });

          // Close the user menu when clicking outside
          window.addEventListener('click', function (e) {
            const menu = document.getElementById('user-menu');
            const button = document.getElementById('user-menu-button');
            if (!button.contains(e.target) && !menu.contains(e.target)) {
              menu.classList.add('hidden');
            }
          });
        </script>
      <?php else: ?>
        <a href="login.php" class="text-sm/6 font-semibold text-white ">Log in / Sign up <span
            aria-hidden="true">&rarr;</span></a>
      <?php endif; ?>

    </div>
  </nav>
  <el-dialog>
    <dialog id="mobile-menu" class="backdrop:bg-transparent lg:hidden">
      <div tabindex="0" class="fixed inset-0 focus:outline-none">
        <el-dialog-panel
          class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-gray-900 p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-100/10">
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5">
              <span class="sr-only">
                Kape Milagrosa
              </span>
              <img src="images/kape_milag.jpg" alt="Kape Milagrosa" class="h-8 w-auto" />
            </a>
            <button type="button" command="close" commandfor="mobile-menu"
              class="-m-2.5 rounded-md p-2.5 text-gray-200">
              <span class="sr-only">Close menu</span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                aria-hidden="true" class="size-6">
                <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </button>
          </div>
          <div class="mt-6 flow-root">
            <div class="-my-6 divide-y divide-white/10">
              <div class="space-y-2 py-6">
                <a href="index.php"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Home</a>
                <a href="products.php"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Products</a>
                <a href="about.php"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">About
                  Us</a>
                <a href="#contact-us"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Contact
                  Us</a>
              </div>
              <div class="py-6">
                <?php if ($user_id && $user): ?>
                  <div class="flex flex-col gap-4">
                    <a href="wishlist.php"
                      class="-mx-3 flex items-center gap-2 rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">
                      <span class="sr-only">Wishlist</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                        aria-hidden="true" class="size-6">
                        <path
                          d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>Wishlist</span>
                      <?php if ($wishlist_count > 0): ?>
                        <span
                          class="inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                          <?= $wishlist_count ?>
                        </span>
                      <?php endif; ?>
                    </a>
                    <a href="cart.php"
                      class="-mx-3 flex items-center gap-2 rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">
                      <span class="sr-only">Cart</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                        aria-hidden="true" class="size-6">
                        <path
                          d="M2.25 2.25h1.386c.51 0 .955.343 1.087.835l1.513 6.435M7.5 14.25a3 3 0 1 0 0 6 3 3 0 0 0 0-6Zm10.5 0a3 3 0 1 0 0 6 3 3 0 0 0 0-6ZM7.5 14.25h10.5m-10.5 0L6.16 5.59m11.34 8.66L19.5 6M6.16 5.59l-.31-1.91a1.125 1.125 0 0 0-1.087-.835H3.375M19.5 6l-2.4-2.4a1.125 1.125 0 0 0-1.087-.835h-7.26"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>Cart</span>
                      <?php if ($cart_count > 0): ?>
                        <span
                          class="inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                          <?= $cart_count ?>
                        </span>
                      <?php endif; ?>
                    </a>
                    <a href="orders.php"
                      class="-mx-3 flex items-center gap-2 rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">
                      <span class="sr-only">Orders</span>
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                        aria-hidden="true" class="size-6">
                        <path
                          d="M3 3v1.5M3 21v-6m0 0l2.5-2.5m-2.5 2.5L5.5 9M21 3v1.5M21 21v-6m0 0l-2.5-2.5m2.5 2.5L18.5 9M3 8.25h18M3 12h18m-9 8.25h9"
                          stroke-linecap="round" stroke-linejoin="round" />
                      </svg>
                      <span>Orders</span>
                      <?php if ($orders_count > 0): ?>
                        <span
                          class="inline-flex items-center justify-center rounded-full bg-red-600 px-2 py-1 text-xs font-bold leading-none text-white">
                          <?= $orders_count ?>
                        </span>
                      <?php endif; ?>
                    </a>
                    <a href="profile.php"
                      class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Your
                      Profile</a>
                    <a href="logout.php"
                      class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Sign
                      out</a>
                  </div>
                <?php else: ?>
                  <a href="login.php"
                    class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Log
                    in / Sign up</a>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </el-dialog-panel>
      </div>
    </dialog>
  </el-dialog>
</header>