<?php
ob_start(); // Start output buffering to prevent premature output

require_once 'enums/OrderStatusEnum.php';

use enums\OrderStatusEnum;

$completedStatus = OrderStatusEnum::Completed;

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Set default store selection if not set
if (!isset($_SESSION['store_code'])) {
  $_SESSION['store_code'] = 'KM';
  header("Location: index.php"); // Redirect to the home page
  exit(); // Always exit after redirect
}

$store_data = [
  'KM' => [
    'name' => 'Kape Milagrosa',
    'logo' => 'images/kape_milag.jpg',
  ],
  'ANB' => [
    'name' => 'Anak ng Birhen',
    'logo' => 'images/anak_milag.png'
  ]
];

// get current store info
$current_store = $store_data[$_SESSION['store_code']];

// get user id
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// set type based on store code
$type = $_SESSION['store_code'] === 'KM' ? 'coffee' : 'online';

$category = $_GET['category'] ?? '';
$query = "SELECT * FROM `products` WHERE `status` = 'active' AND `type` = ? AND `category` != 'Add-ons' ORDER BY id DESC LIMIT 6";
$params = [$type];

if (!empty($category)) {
  $query = "SELECT * FROM `products` WHERE `status` = 'active' AND `type` = ? AND `category` = ? ORDER BY id DESC LIMIT 6";
  $params[] = $category;
}

$select_products = $conn->prepare($query);
$select_products->execute($params);
$products = $select_products->fetchAll(PDO::FETCH_ASSOC);

$limit = 8;

// get popular products this week
$select_popular = $conn->prepare("
    SELECT 
        op.product_id, 
        p.name AS product_name, 
        p.image AS product_image,
        p.price,
        SUM(op.quantity) AS total_quantity
    FROM order_products op 
    JOIN products p ON op.product_id = p.id 
    JOIN orders o ON op.order_id = o.id
    WHERE o.status = ?
      AND p.type = ?
      AND o.placed_on >= NOW() - INTERVAL 7 DAY
    GROUP BY op.product_id, p.name, p.image, p.price
    ORDER BY total_quantity DESC 
    LIMIT 
" . $limit); 

// assuming $completedStatus->value is the first param, and $type is second
$select_popular->execute([$completedStatus->value, $type]);

$popular_products = $select_popular->fetchAll(PDO::FETCH_ASSOC);

// if there's no popular products this week, get random products
if (count($popular_products) === 0) {
  $select_random = $conn->prepare("
      SELECT 
          id AS product_id,
          name AS product_name,
          image AS product_image,
          price
      FROM `products` 
      WHERE `status` = 'active' 
        AND `type` = ? 
        AND `category` != 'Add-ons'
      ORDER BY RAND() 
      LIMIT 
  ". $limit);

  $select_random->execute([$type]);
  $popular_products = $select_random->fetchAll(PDO::FETCH_ASSOC);
}

// if the popular products are less than $limit, fill the rest with random products
if (count($popular_products) < $limit) {
  $needed = 5 - count($popular_products);
  $existing_ids = array_column($popular_products, 'product_id');

  $query = "
      SELECT 
          id AS product_id,
          name AS product_name,
          image AS product_image,
          price
      FROM `products` 
      WHERE `status` = 'active' 
        AND `type` = ? 
        AND `category` != 'Add-ons'
  ";

  $params = [$type];

  if (!empty($existing_ids)) {
    $placeholders = implode(',', array_fill(0, count($existing_ids), '?'));
    $query .= " AND id NOT IN ($placeholders)";
    $params = array_merge($params, $existing_ids);
  }

  // ✅ inject LIMIT safely
  $query .= " ORDER BY RAND() LIMIT $needed";

  $select_additional = $conn->prepare($query);
  $select_additional->execute($params);
  $additional_products = $select_additional->fetchAll(PDO::FETCH_ASSOC);

  $popular_products = array_merge($popular_products, $additional_products);
}

// get user info and 
if ($user_id) {
  $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
  $select_user->execute([$user_id]);
  $user = $select_user->fetch(PDO::FETCH_ASSOC);
}



ob_end_flush(); // Flush the output buffer and turn off output buffering

?>

<header class="absolute inset-x-0 top-0 z-50">
  <nav aria-label="Global" class="flex items-center justify-between p-8 lg:px-15">
    <div class="flex lg:flex-1">
      <a href="#" class="-m-1.5 p-1.5">
        <span class="sr-only"><?= $current_store['name'] ?></span>
        <img src="<?= $current_store['logo'] ?>" alt="" class="h-10 w-auto rounded-full" />
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
      <a href="#" class="text-sm/6 font-semibold text-white">Home</a>
      <a href="#" class="text-sm/6 font-semibold text-white">Products</a>

      <a href="#" class="text-sm/6 font-semibold text-white">About Us</a>
      <a href="#contact-us" class="text-sm/6 font-semibold text-white">Contact Us</a>
    </div>
    <div class="hidden lg:flex lg:flex-1 lg:justify-end">
      <a href="#" class="text-sm/6 font-semibold text-white">Log in / Sign up <span aria-hidden="true">&rarr;</span></a>
    </div>
  </nav>
  <el-dialog>
    <dialog id="mobile-menu" class="backdrop:bg-transparent lg:hidden">
      <div tabindex="0" class="fixed inset-0 focus:outline-none">
        <el-dialog-panel
          class="fixed inset-y-0 right-0 z-50 w-full overflow-y-auto bg-gray-900 p-6 sm:max-w-sm sm:ring-1 sm:ring-gray-100/10">
          <div class="flex items-center justify-between">
            <a href="#" class="-m-1.5 p-1.5">
              <span class="sr-only"><?= $current_store['name'] ?></span>
              <img src="<?= $current_store['logo'] ?>" alt="" class="h-8 w-auto" />
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
                <a href="#"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Home</a>
                <a href="#"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Products</a>
                <a href="#"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">About
                  Us</a>
                <a href="#contact-us"
                  class="-mx-3 block rounded-lg px-3 py-2 text-base/7 font-semibold text-white hover:bg-white/5">Contact
                  Us</a>
              </div>
              <div class="py-6">
                <a href="#"
                  class="-mx-3 block rounded-lg px-3 py-2.5 text-base/7 font-semibold text-white hover:bg-white/5">Log
                  in / Sign up</a>
              </div>
            </div>
          </div>
        </el-dialog-panel>
      </div>
    </dialog>
  </el-dialog>
</header>