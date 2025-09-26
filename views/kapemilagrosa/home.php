<?php
require_once 'helpers/FormatHelper.php';

use Helpers\FormatHelper;

$storeCode = $_SESSION['store_code'] ?? '';
$type = $storeCode === 'KM' ? 'coffee' : 'online';

// fetch favorite products of user
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];


  // get the ids of the products in the wishlist
  $select_wishlist = $conn->prepare("SELECT product_id FROM `wishlist` WHERE user_id = ? AND type = ?");
  $select_wishlist->execute([$user_id, $type]);
  $wishlist_product_ids = $select_wishlist->fetchAll(PDO::FETCH_COLUMN);
}
$categories = [];
if ($type == 'coffee') {
  // predefined categories for coffee
  $categories = [
    [
      'name' => 'Frappe',
      'image' => 'Frappe.jpg',
      'description' => 'Creamy blended coffee drinks topped with whipped cream.',
    ],
    [
      'name' => 'Fruit Soda',
      'image' => 'Fruit Soda.jpg',
      'description' => 'Refreshing carbonated drinks with fruity flavors.',
    ],

    [
      'name' => 'Frappe Extreme',
      'image' => 'Frappe Extreme.jpg',
      'description' => 'Extra indulgent frappes with bold flavors and toppings.',
    ],
    [
      'name' => 'Milk Tea',
      'image' => 'Milk Tea.jpg',
      'description' => 'Classic tea mixed with creamy milk and chewy pearls.',
    ],
    [
      'name' => 'Fruit Tea',
      'image' => 'Fruit Tea.jpg',
      'description' => 'Light and refreshing teas infused with real fruits.',
    ],
    [
      'name' => 'Fruit Milk',
      'image' => '',
      'description' => 'Smooth, chilled milk blended with fresh fruit flavors.',
    ],
    [
      'name' => 'Espresso',
      'image' => 'Fruit Milk.jpg',
      'description' => 'Strong, rich shots of pure coffee perfection.',
    ],
    [
      'name' => 'Hot Non-Coffee',
      'image' => 'Hot Non-Coffee.png',
      'description' => 'Warm drinks without coffee for cozy moments.',
    ],
    [
      'name' => 'Iced Non-Coffee',
      'image' => 'Iced Non-Coffee.jpg',
      'description' => 'Chilled beverages for non-coffee lovers.',
    ],
    [
      'name' => 'Snacks',
      'image' => 'Snacks.jpg',
      'description' => 'Delicious bites to pair with your favorite drinks.',
    ],
  ];


  // fetch top categories for coffee
  $temp_top_categories = $conn->prepare("SELECT p.*, SUM(op.quantity) AS total_quantity
    FROM products p
    JOIN order_products op ON p.id = op.product_id
    WHERE p.type = ? 
    GROUP BY p.id
    ORDER BY total_quantity DESC
    LIMIT 3");
  $temp_top_categories->execute([$type]);
  $temp_top_categories = $temp_top_categories->fetchAll(PDO::FETCH_ASSOC);

  // if no top categories, use predefined ones
  if (count($temp_top_categories) === 0) {
    $temp_top_categories = array_slice($categories, 0, 3);
  } else {
    // get the categories
    $top_categories = [];
    foreach ($temp_top_categories as $product) {
      $category_name = $product['category'];
      // find the category info from predefined categories
      $category_info = array_filter($categories, fn($cat) => $cat['name'] === $category_name);
      if ($category_info) {
        $top_categories[] = array_merge($product, array_values($category_info)[0]);
      }
    }

    // if less than 3 top categories, fill the rest with predefined ones
    if (count($top_categories) < 3) {
      $existing_category_names = array_column($top_categories, 'category');
      foreach ($categories as $cat) {
        if (!in_array($cat['name'], $existing_category_names)) {
          $top_categories[] = $cat;
          if (count($top_categories) >= 3)
            break;
        }
      }
    }
  }

}

?>

<section class="page-bg relative">
  <div class="mx-auto w-screen max-w-screen-xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32 h-screen flex items-center">
    <div class="max-w-prose text-left">
      <!-- Main Heading -->
      <h1 class="text-4xl font-bold text-white sm:text-5xl">
        Enjoy Every Sip
      </h1>

      <!-- Subheading -->
      <p class="mt-4 text-xl text-white sm:text-2xl">
        Discover the Best Beverages for You
      </p>

      <!-- Description -->
      <p class="mt-4 text-base text-pretty text-white sm:text-lg/relaxed">
        Explore our wide range of drinks, from frappes to milk teas, fruit sodas, and more.
      </p>

      <!-- Buttons -->
      <?php

      ?>
      <div class="mt-4 flex gap-4 sm:mt-6">
        <!-- Primary Button -->
        <a class="inline-block rounded border-color bg-color px-5 py-3 font-medium text-white shadow-sm transition-colors bg-hover-color"
          href="#">
          Order Now
        </a>

        <!-- Secondary Button -->
        <a class="inline-block rounded border border-gray-200 px-5 py-3 font-medium text-gray-100 shadow-sm transition-colors hover:bg-gray-50 text-hover-color"
          href="#">
          Learn More
        </a>
      </div>

    </div>
  </div>

  <div class="absolute bottom-0 w-full bg-white/30 backdrop-blur-sm py-8">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-4 h-[300px]">
      <div class="lg:col-span-1 h-full">
        <div class="flex h-full items-center justify-center rounded text-color">
          <h2 class="text-4xl font-bold sm:text-3xl [writing-mode:vertical-rl] rotate-180">
            Top Categories
          </h2>
        </div>
      </div>

      <div class="lg:col-span-3 grid gap-4 sm:grid-cols-3">
        <?php foreach ($top_categories as $top_category): ?>
          <a href="#"
            class="relative block overflow-hidden rounded group border border-white/50 bg-white/30 backdrop-blur-sm">

            <img src="images/<?= htmlspecialchars($top_category['image'] ?: 'default.jpg') ?>"
              alt="<?= htmlspecialchars($top_category['name']) ?>"
              class="absolute inset-0 h-full w-full object-cover opacity-75 transition-opacity group-hover:opacity-50" />

            <div class="relative h-48 p-4 sm:p-6 lg:p-8 flex flex-col justify-end">
              <!-- <p class="text-sm font-medium tracking-widest text-pink-500 uppercase">
                  <?= htmlspecialchars($top_category['name']) ?>
                </p> -->

              <p class="text-xl font-bold text-color sm:text-2xl">
                <?= htmlspecialchars($top_category['name']) ?>
              </p>

              <div class="translate-y-8 opacity-0 transition-all group-hover:translate-y-0 group-hover:opacity-100 mt-2">
                <p class="text-sm text-white">
                  <?= htmlspecialchars($top_category['description'] ?? 'Lorem ipsum dolor sit amet.') ?>
                </p>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<section class="my-10">
  <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <header class="text-center">
      <h2 class="text-xl font-bold text-color sm:text-3xl">
        Our Best Sellers
      </h2>

      <p class="mx-auto mt-4 max-w-md text-gray-500">
        Our most popular products this week. Order now and get it delivered to your doorstep!
      </p>
    </header>

    <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <?php foreach ($popular_products as $popular_product): ?>
        <li>
          <div class="group relative block overflow-hidden">
            <!-- Wishlist Button -->
            <?php $is_in_wishlist = isset($wishlist_product_ids) && in_array($popular_product['product_id'], $wishlist_product_ids); ?>
            <button type="button"
              class="wishlist-btn absolute end-4 top-4 z-10 rounded-full bg-white p-1.5 text-gray-900 transition hover:text-gray-900/75 hover:shadow-lg hover:scale-105 <?= $is_in_wishlist ? 'active' : '' ?>"
              data-product-id="<?= $popular_product['product_id'] ?>" type="button">
              <span class="sr-only">Wishlist</span>
              <svg xmlns="http://www.w3.org/2000/svg" fill="<?= $is_in_wishlist ? 'red' : 'none' ?>" viewBox="0 0 24 24"
                stroke-width="1.5" stroke="<?= $is_in_wishlist ? 'red' : 'currentColor' ?>" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
            </button>

            <!-- Product Image -->
            <img src="uploaded_img/<?= htmlspecialchars($popular_product['product_image']) ?>"
              alt="<?= htmlspecialchars($popular_product['product_name']) ?>"
              class="h-64 w-full object-cover transition duration-500 group-hover:scale-105 sm:h-72" />

            <div class="relative border border-gray-100 bg-white p-6">
              <!-- Price -->
              <p class="text-gray-700">
                ₱<?= number_format($popular_product['price'] ?? 0, 2) ?>
              </p>

              <!-- Name -->
              <h3 class="mt-1.5 text-lg font-medium text-gray-900">
                <?= htmlspecialchars($popular_product['product_name']) ?>
              </h3>

              <!-- Optional description or stats
              <p class="mt-1.5 line-clamp-3 text-gray-700">
                Sold <?= $popular_product['total_quantity'] ?? 0 ?> this week
              </p> -->

              <!-- Action Buttons -->
              <form class="mt-4 flex gap-4">
                <button type="button"
                  class="block w-full rounded-sm bg-gray-100 px-4 py-3 text-sm font-medium text-gray-900 transition hover:scale-105 cart-btn"
                  data-product-id="<?= $popular_product['product_id'] ?>">
                  Add to Cart
                </button>

                <button type="button"
                  class="block w-full rounded-sm bg-color px-4 py-3 text-sm font-medium text-white transition hover:scale-105 buy-btn"
                  data-product-id="<?= $popular_product['product_id'] ?>">
                  Buy Now
                </button>
              </form>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<section id="contact-us" class="my-10">
  <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-8">
      <div class="lg:col-span-2 ">
        <div class="p-5 lg:p-10 rounded bg-gray-100">
          <div class="mb-5">
            <h2 class="text-2xl font-bold text-color sm:text-3xl">
              Contact Us
            </h2>
            <p class="mt-4 text-gray-500">
              Have questions or feedback? We'd love to hear from you! Fill out the form below to get in touch with us.
            </p>
          </div>
          <div class="mb-3">
            <div class="mb-3">
              <label for="name">
                <span class="text-sm font-medium text-color">Name </span>
                <input type="text" id="name" name="name"
                  class="mt-0.5 w-full h-[35px] p-3 rounded border-color shadow-sm sm:text-sm" />
              </label>
            </div>
            
            <div class="mb-3">
              <label for="email">
                <span class="text-sm font-medium text-color">Email </span>
                <input type="text" id="email" name="email"
                  class="mt-0.5 w-full h-[35px] p-3 rounded border-color shadow-sm sm:text-sm" />
              </label>
            </div>
            <div class="mb-3">
              <label for="contact">
                <span class="text-sm font-medium text-color">Contact no.</span>
                <input type="text" id="contact" name="contact"
                  class="mt-0.5 w-full h-[35px] p-3 rounded border-color shadow-sm sm:text-sm" />
              </label>
            </div>
            <div class="mb-3">
              <label for="message">
                <span class="text-sm font-medium text-color">Message </span>
                <textarea id="message" name="message" rows="4"
                  class="mt-0.5 w-full p-3 rounded border-color shadow-sm sm:text-sm"></textarea>
              </label>
            </div>
          </div>
          <div class="mt-3 text-right">
            <button
              class="rounded border-color bg-color px-5 py-2 font-medium text-white shadow-sm transition-colors bg-hover-color">
              Send Message
            </button>
          </div>
        </div>
      </div>
      <div class="rounded">
        <div class="rounded">
        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15474.732619958557!2d121.32509100000001!3d14.154716!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33bd5f36a2223d53%3A0xe436a7bc94d7554d!2sKAPE%20Milagrosa!5e0!3m2!1sen!2sph!4v1758873496270!5m2!1sen!2sph" 
          class="w-full " height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
      </div>
    </div>
  </div>

</section>

<!-- sweet alert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // // Show alert if message is set
  // <?php if (isset($_SESSION[''])): ?>
    //   Swal.fire({
    //     icon: 'success',
    //     title: '<?= $message ?>',
    //     showConfirmButton: false,
    //     timer: 1500
    //   });
    // <?php endif; ?>
  console.log(<?= json_encode($top_categories) ?>);
  console.log(<?= json_encode($categories) ?>);
  console.log(<?= json_encode($type) ?>);
</script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.wishlist-btn').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.dataset.productId;

        // Toggle filled heart locally
        const svg = this.querySelector('svg');
        const isActive = this.classList.toggle('active');

        if (isActive) {
          // turn heart red
          svg.setAttribute('fill', 'red');
          svg.setAttribute('stroke', 'red');
        } else {
          // back to outline
          svg.setAttribute('fill', 'none');
          svg.setAttribute('stroke', 'currentColor');
        }

        // Optionally send AJAX to server
        fetch('<?= FormatHelper::formatPath('index.php', 'services/wishlist.php') ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=add_to_wishlist&product_id=${productId}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'error') {
              if (data.redirect) {
                window.location.href = data.redirect;
              }
            } else if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
              });

            }
          })
          .catch(err => console.error(err));
      });
    });
    document.querySelectorAll('.cart-btn').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.dataset.productId;

        fetch('<?= FormatHelper::formatPath('index.php', 'services/cart.php') ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=add_to_cart&product_id=${productId}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'error') {
              if (data.redirect) {
                window.location.href = data.redirect;
              }
            } else if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
              });
            }
          })
          .catch(err => console.error(err));
      });
    });
    document.querySelectorAll('.buy-btn').forEach(button => {
      button.addEventListener('click', function () {
        const productId = this.dataset.productId;

        fetch('<?= FormatHelper::formatPath('index.php', 'services/cart.php') ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: `action=add_to_cart&product_id=${productId}`
        })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'error') {
              if (data.redirect) {
                window.location.href = data.redirect;
              }
            } else if (data.status === 'success') {
              // Redirect to cart page
              window.location.href = 'cart.php';
            }
          })
          .catch(err => console.error(err));
      });
    });
  });
</script>