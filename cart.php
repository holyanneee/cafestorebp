<?php

@include 'config.php';
session_start();

// check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// get cart items from based on user_id
$user_id = $_SESSION['user_id'];
$cart_items = [];
if (isset($conn)) {
  $stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image, products.cup_sizes as product_cup_sizes, products.type
                               FROM cart 
                               JOIN products ON cart.product_id = products.id 
                               WHERE cart.user_id = ?
                           ORDER BY cart.id DESC");
  $stmt->execute([$user_id]);
  $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cart | Kape Milagrosa
  </title>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <link rel="stylesheet" href="css/new_style.css">
</head>

<body class="min-h-screen font-sans text-gray-900">
  <?php include 'header.php'; ?>

  <main>

    <section class="mt-20">
      <section>
        <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
          <div class="mx-auto max-w-3xl">
            <header class="text-center">
              <h1 class="text-xl font-bold text-gray-900 sm:text-3xl">Your Cart</h1>
            </header>

            <div class="mt-8">
              <ul class="space-y-4">
                <?php if (!empty($cart_items)): ?>
                  <?php
                  $total = 0;
                  foreach ($cart_items as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                    if ($item['type'] === 'coffee') {
                      $product_cup_sizes = isset($item['product_cup_sizes']) ? json_decode($item['product_cup_sizes'], true) : [];
                      $cup_size = json_decode($item['cup_size'], true)['size'];

                      $cup_size_price = json_decode($item['cup_size'], true)['price'];


                      $ingredient_choices = isset($item['ingredients']) ? (json_decode($item['ingredients'], true)) : [];
                      $add_ons = isset($item['add_ons']) ? (json_decode($item['add_ons'], true)) : [];
                      $quantity = $item['quantity'] ?? 1;
                      $special_instructions = isset($item['special_instructions']) ? htmlspecialchars($item['special_instructions'], ENT_QUOTES) : '';
                    }
                    ?>
                    <li class="flex items-center gap-4 pb-4">
                      <img src="uploaded_img/<?= htmlspecialchars($item['image']); ?>"
                        alt="<?= htmlspecialchars($item['name']); ?>" class="size-16 rounded-sm object-cover" />

                      <div class="flex-1">
                        <h3 class="text-sm text-gray-900 font-semibold"><?= htmlspecialchars($item['name']); ?></h3>

                        <dl class="mt-1 space-y-px text-[11px] text-gray-600">
                          <div>
                            <dt class="inline">Price:</dt>
                            <dd class="inline">₱<?= number_format($item['price'], 2); ?></dd>
                          </div>
                          <div>
                            <dt class="inline">Quantity:</dt>
                            <dd class="inline"><?= $item['quantity']; ?></dd>
                          </div>
                          <div>
                            <dt class="inline">Subtotal:</dt>
                            <dd class="inline">₱<?= number_format($subtotal, 2); ?></dd>
                          </div>
                        </dl>
                      </div>

                      <div class="flex items-center gap-2">

                        <!-- Update Button -->
                        <button type="button" data-te-toggle="modal" data-te-target="#updateModal<?= $item['id']; ?>"
                          class="text-gray-600 transition hover:text-blue-600">
                          <span class="sr-only">Update item</span>
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.93zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                          </svg>
                        </button>

                        <!-- Modal -->
                        <div class="hidden fixed inset-0 z-50 grid place-content-center bg-black/50 p-4"
                          id="updateModal<?= $item['id']; ?>" tabindex="-1" aria-modal="true" role="dialog"
                          aria-labelledby="modalTitle<?= $item['id']; ?>">
                          <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg relative">
                            <!-- Close Button -->
                            <button type="button" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600"
                              data-te-dismiss="modal" aria-label="Close">
                              ✕
                            </button>

                            <h2 id="modalTitle<?= $item['id']; ?>" class="text-xl font-bold text-gray-900 sm:text-2xl">
                              Update <?= htmlspecialchars($item['name']); ?>
                            </h2>

                            <div class="mt-4">
                              <!-- Put your update form fields here -->
                              <form action="update_cart.php" method="POST">
                                <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                                <label for="qty<?= $item['id']; ?>" class="block text-sm text-gray-700">Quantity</label>
                                <input type="number" min="1" name="quantity" id="qty<?= $item['id']; ?>"
                                  value="<?= $item['quantity']; ?>"
                                  class="mt-1 w-full rounded border-gray-300 text-gray-700" />
                                <!-- add any other fields here -->
                              </form>
                            </div>

                            <footer class="mt-6 flex justify-end gap-2">
                              <button type="button"
                                class="rounded bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
                                data-te-dismiss="modal">
                                Cancel
                              </button>

                              <button type="submit" form="updateForm<?= $item['id']; ?>"
                                class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                Update
                              </button>
                            </footer>
                          </div>
                        </div>

                        <form action="remove_cart.php" method="post">
                          <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                          <button type="submit" class="text-gray-600 transition hover:text-red-600">
                            <span class="sr-only">Remove item</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                              stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                         m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084
                         a2.25 2.25 0 01-2.244-2.077L4.772 5.79
                         m14.456 0a48.108 48.108 0 00-3.478-.397" />
                            </svg>
                          </button>
                        </form>
                      </div>
                    </li>
                    <!-- update modal -->


                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="text-center text-gray-500 py-6">Your cart is empty.</li>
                <?php endif; ?>
              </ul>

              <!-- Cart Total -->
              <div class="mt-8 flex justify-end border-t border-gray-100 pt-8">
                <div class="w-screen max-w-lg space-y-4">
                  <dl class="space-y-0.5 text-sm text-gray-700">
                    <div class="flex justify-between !text-base font-medium">
                      <dt>Total</dt>
                      <dd>₱<?= isset($total) ? number_format($total, 2) : '0.00'; ?></dd>
                    </div>
                  </dl>

                  <div class="flex justify-end">
                    <a href="checkout.php"
                      class="block rounded-sm bg-gray-700 px-5 py-3 text-sm text-gray-100 transition hover:bg-gray-600">
                      Checkout
                    </a>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>

    </section>
  </main>

  <?php include 'footer.php'; ?>

  <!-- sweet alert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

  <!-- open modal -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const modalTriggers = document.querySelectorAll('[data-te-toggle="modal"]');
      modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function () {
          const targetModalId = this.getAttribute('data-te-target');
          const targetModal = document.querySelector(targetModalId);
          if (targetModal) {
            targetModal.classList.remove('hidden');
          }
        });
      });

      const modalCloseButtons = document.querySelectorAll('[data-te-dismiss="modal"]');
      modalCloseButtons.forEach(button => {
        button.addEventListener('click', function () {
          const modal = this.closest('.fixed.inset-0');
          if (modal) {
            modal.classList.add('hidden');
          }
        });
      });
    });
  </script>
</body>

</html>