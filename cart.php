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
          <header class="flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900 sm:text-3xl">Your Cart</h1>
          </header>

          <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-8">
            <div class=" lg:col-span-2">
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
                        <li class="flex items-center gap-4">
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
                            <button command="show-modal" commandfor="update<?= $item['id']; ?>"
                              class="text-gray-600 transition hover:text-blue-600">
                              <span class="sr-only">Update item</span>
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.93zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                              </svg>
                            </button>
                            <el-dialog>
                              <dialog id="update<?= $item['id']; ?>" aria-labelledby="dialog-title"
                                class="fixed inset-0 size-auto max-h-none max-w-none overflow-y-auto bg-transparent backdrop:bg-transparent">
                                <el-dialog-backdrop class="fixed inset-0 bg-gray-500/75 transition-opacity data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in"></el-dialog-backdrop>

                                <div tabindex="0"
                                  class="flex min-h-full items-end justify-center p-4 text-center focus:outline-none sm:items-center sm:p-0">
                                  <el-dialog-panel
                                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all data-closed:translate-y-4 data-closed:opacity-0 data-enter:duration-300 data-enter:ease-out data-leave:duration-200 data-leave:ease-in sm:my-8 sm:w-full sm:max-w-lg data-closed:sm:translate-y-0 data-closed:sm:scale-95">
                                    <header>
                                      <h2 class="text-lg font-medium leading-6 text-gray-900 p-4 border-b">
                                      Customize <?= htmlspecialchars($item['name']); ?>
                                      </h2>
                                    <div class="bg-white p-3 sm:p-2">
                                      
                                    </div>

                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                      <button type="button" command="close" commandfor="update<?= $item['id']; ?>"
                                        class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-red-500 sm:ml-3 sm:w-auto">Deactivate</button>
                                      <button type="button" command="close" commandfor="update<?= $item['id']; ?>"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs inset-ring inset-ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                                    </div>
                                  </el-dialog-panel>
                                </div>
                              </dialog>
                            </el-dialog>
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
                        </li>


                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="text-center text-gray-500 py-6">Your cart is empty.</li>
                <?php endif; ?>
              </ul>

            </div>
            <div class="">

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

</body>

</html>