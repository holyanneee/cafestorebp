<?php

@include 'config.php';
session_start();

// check if the user is logged in
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

// get cart items from based on user_id
$user_id = $_SESSION['user_id'];
$cart_items = [];

$type = isset($_GET['type']) && in_array($_GET['type'], ['coffee', 'religious']) ? $_GET['type'] : 'coffee';

$stmt = $conn->prepare("SELECT cart.*, products.name, products.price, products.image, products.cup_sizes as product_cup_sizes, products.type
                               FROM cart 
                               JOIN products ON cart.product_id = products.id 
                               WHERE cart.user_id = ? AND cart.type = ?
                           ORDER BY cart.id DESC");
$stmt->execute([$user_id, $type]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total products (considering quantities)
$total_products = count($cart_items) + (array_sum(array_column($cart_items, 'quantity')) - count($cart_items));


// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//   $action = $_POST['action'] ?? '';
//   if ($action === 'remove_from_cart') {
//     $cart_id = isset($_POST['cart_id']) ? (int) $_POST['cart_id'] : 0;
//     if ($cart_id > 0) {
//       // Verify ownership before deletion
//       $verifyStmt = $conn->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1");
//       $verifyStmt->execute([$cart_id, $user_id]);
//       $cartItem = $verifyStmt->fetch(PDO::FETCH_ASSOC);
//       if ($cartItem) {
//         $delStmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
//         $delStmt->execute([$cart_id, $user_id]);
//         $_SESSION['alert'] = ['type' => 'success', 'message' => 'Item removed from cart!'];
//       } else {
//         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Item not found in your cart.'];
//       }
//     } else {
//       $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid cart item.'];
//     }
//   }

//   if ($action === 'update_cart_item') {
//     $cart_id = isset($_POST['cart_id']) ? (int) $_POST['cart_id'] : 0;
//     $quantity = isset($_POST['quantity']) ? max(1, (int) $_POST['quantity']) : 1; // Minimum quantity of 1
//     if ($cart_id > 0) {
//       // Verify ownership before update
//       $verifyStmt = $conn->prepare("SELECT * FROM cart WHERE id = ? AND user_id = ? LIMIT 1");
//       $verifyStmt->execute([$cart_id, $user_id]);
//       $cartItem = $verifyStmt->fetch(PDO::FETCH_ASSOC);
//       if ($cartItem) {
//         // Fetch product price
//         $productStmt = $conn->prepare("SELECT price FROM products WHERE id = ? LIMIT 1");
//         $productStmt->execute([$cartItem['product_id']]);
//         $product = $productStmt->fetch(PDO::FETCH_ASSOC);
//         if ($product) {
//           $newSubtotal = $product['price'] * $quantity; // or use cup price if coffee
//           $updStmt = $conn->prepare("UPDATE cart SET quantity = ?, subtotal = ? WHERE id = ? AND user_id = ?");
//           $updStmt->execute([$quantity, $newSubtotal, $cart_id, $user_id]);
//           $_SESSION['alert'] = ['type' => 'success', 'message' => 'Cart updated successfully!'];
//         } else {
//           $_SESSION['alert'] = ['type' => 'error', 'message' => 'Product not found.'];
//         }
//       } else {
//         $_SESSION['alert'] = ['type' => 'error', 'message' => 'Item not found in your cart.'];
//       }
//     } else {
//       $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid cart item.'];
//     }
//   }

//   // Redirect to avoid form resubmission
//   header('Location: cart.php?type=' . urlencode($type));
//   exit;
// }
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
        <div
          class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8 <?= count($cart_items) === 0 || count($cart_items) < 3 ? 'min-h-[60vh]' : '' ?>">
          <header class="flex items-center justify-between">
            <h1 class="text-xl text-color font-bold text-gray-900 sm:text-3xl">Your Cart</h1>
            <form method="get" class="flex flex-wrap gap-3" id="filters">
              <!-- Type Filter -->
              <select name="type"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                id="type">
                <option value="coffee" <?= $type === 'coffee' ? 'selected' : ''; ?>>Coffee</option>
                <option value="religious" <?= $type === 'religious' ? 'selected' : ''; ?>>Religious Items
                </option>
              </select>
            </form>
          </header>

          <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-8 mt-8">
            <div class=" lg:col-span-2">
              <ul class="space-y-4">
                <?php if (!empty($cart_items)): ?>
                  <?php
                  $total = 0;
                  foreach ($cart_items as $item):
                    $subtotal = $item['price'] * $item['quantity'];
              
                    $isTypeCoffee = $item['type'] === 'coffee';
                    if ($isTypeCoffee) {
                      $product_cup_sizes = isset($item['product_cup_sizes']) ? json_decode($item['product_cup_sizes'], true) : [];
                      $cup_size = isset($item['cup_size']) ? json_decode($item['cup_size'], true)['size'] : 'Regular';
                      $cup_size_price = isset($item['cup_size']) ? (json_decode($item['cup_size'], true)['price']) : 0;
                      $product_ingredients = isset($item['ingredients']) ? (json_decode($item['ingredients'], true)) : [];
                      $stmt = $conn->prepare("SELECT * FROM products WHERE category = 'Add-ons'");
                      $stmt->execute();
                      $add_ons = $stmt->fetchAll(PDO::FETCH_ASSOC);
                      $selected_add_ons = isset($item['add_ons']) ? json_decode($item['add_ons'], true) : [];

                      $subtotal += $cup_size_price * $item['quantity'];
                      foreach ($selected_add_ons as $add_on) {
                        $subtotal += $add_on['price'] * $item['quantity'];
                      }

                      $special_instructions = isset($item['special_instruction']) ? htmlspecialchars($item['special_instruction'], ENT_QUOTES) : '';
                    
                    }
                    $total += $subtotal;
                    ?>
                    <li class="flex items-center gap-4">
                      <img src="uploaded_img/<?= htmlspecialchars($item['image']); ?>"
                        alt="<?= htmlspecialchars($item['name']); ?>" class="size-30 rounded-sm object-cover" />
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
                            <?php if ($isTypeCoffee): ?>
                              <dt class="inline">Cup Size:</dt>
                              <dd class="inline"><?= htmlspecialchars(ucfirst($cup_size)); ?> (+
                                ₱<?= number_format($cup_size_price, 2); ?>)</dd>
                              <?php $subtotal ?>
                            <?php endif; ?>
                          </div>
                          <div>
                            <dt class="inline">Subtotal:</dt>
                            <dd class="inline">₱<?= number_format($subtotal, 2); ?></dd>
                          </div>
                        </dl>
                      </div>

                      <div class="flex items-center gap-2">


                        <?php if ($isTypeCoffee): ?>
                          <button command="show-modal" commandfor="customize-modal-<?= $item['id'] ?>"
                            class="text-gray-600 transition hover:text-blue-600">
                            <span class="sr-only">Customize item</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                              stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.93zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                          </button>
                          <?php include 'cart_customize_modal.php'; ?>
                        <?php else: ?>
                          <form target="" method="post" id="update-quantity-form-<?= $item['id']; ?>">
                            <label for="quantity-<?= $item['id']; ?>" class="sr-only"> Quantity </label>
                            <input type="hidden" name="action" value="update_quantity">
                            <input type="hidden" name="cart_id" value="<?= $item['id']; ?>">
                            <input type="number" min="1" value="<?= $item['quantity']; ?>" id="quantity-<?= $item['id']; ?>"
                              name="quantity-<?= $item['id']; ?>"
                              class="h-8 w-12 rounded-sm border-gray-200 bg-gray-50 p-0 text-center text-xs text-gray-600 [-moz-appearance:_textfield] focus:outline-hidden [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none" />
                          </form>
                        <?php endif; ?>

                        <button type="submit" class="text-gray-600 transition hover:text-red-600"
                          id="remove-item-<?= $item['id'] ?>" data-cart-id="<?= $item['id']; ?>">
                          <span class="sr-only">Remove item</span>
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                                    m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084
                                    a2.25 2.25 0 01-2.244-2.077L4.772 5.79
                                    m14.456 0a48.108 48.108 0 00-3.478-.397" />
                          </svg>
                        </button>

                    </li>


                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="text-center text-gray-500 py-6">Your cart is empty.</li>
                <?php endif; ?>
              </ul>
            </div>
            <!-- cart summary-->
            <div class="rounded-lg border bg-white p-4 shadow-sm">
              <dl class="space-y-1 text-sm text-gray-700">
                <div class="flex justify-between">
                  <dt>Total Products</dt>
                  <dd class="font-semibold text-gray-900"><?= number_format($total_products ?? 0); ?></dd>
                </div>
                <div class="flex justify-between">
                  <dt>Total</dt>
                  <dd class="font-semibold text-gray-900">₱<?= number_format($total ?? 0, 2); ?></dd>
                </div>
              </dl>


              <div class="mt-5 space-y-2">
                <a href="checkout.php?type=<?= $type; ?>"
                  class="block w-full rounded-md bg-color px-3 py-2 text-center text-sm font-semibold text-white shadow-xs hover:bg-indigo-500">
                  Proceed to Checkout
                </a>
                <a href="orders.php"
                  class="block w-full rounded-md bg-gray-100 px-3 py-2 text-center text-sm font-semibold text-gray-700 shadow-xs hover:bg-gray-200">
                  Continue Browsing
                </a>
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
  <script>
    // check if the filters are changed
    document.addEventListener('DOMContentLoaded', () => {
      const filterForm = document.getElementById('filters');

      const typeSelect = document.getElementById('type');

      const initialType = typeSelect.value;

      let filterTimeout = null;

      function checkFilters() {
        const hasChanged =
          typeSelect.value !== initialType;

        if (!hasChanged) return;


        filterForm.submit();

      }

      typeSelect.addEventListener('change', checkFilters);
    });
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const forms = document.querySelectorAll('form[id^="customize-form-"]');

      forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
          e.preventDefault();

          const formData = new FormData(form);
          const modalId = `customize-modal-${formData.get('cart_id')}`;
          const modalElement = document.getElementById(modalId);

          const data = Object.fromEntries(formData.entries());


          try {

            const response = await fetch('services/cart.php', {
              method: 'POST',
              body: formData
            });
            const data = await response.json();

            if (modalElement) {
              if (typeof tailwind !== 'undefined' && tailwind.Modal) {
                const instance = tailwind.Modal.getInstance(modalElement);
                if (instance) {
                  instance.hide();
                } else {
                  // Create new instance if missing
                  const newInstance = new tailwind.Modal(modalElement);
                  newInstance.hide();
                }
              } else {
                // Manual close fallback
                modalElement.classList.add('hidden');
                modalElement.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden'); // restore scroll
              }
            }


            if (data.status === 'success') {
              await Swal.fire({
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
              }).then(() => {
                window.location.reload();
              });

            } else {
              Swal.fire({
                icon: 'error',
                title: data.message || 'An error occurred',
                showConfirmButton: false,
                timer: 1500
              }).then(() => {
                window.location.reload();
              });
            }

          } catch (error) {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'An error occurred',
              showConfirmButton: false,
              timer: 1500
            });
          }
        });
      });
    });
  </script>

  <script>
    // update quantity for non-coffee items
    document.addEventListener('DOMContentLoaded', () => {
      const quantityInputs = document.querySelectorAll('input[id^="quantity-"]');

      quantityInputs.forEach(input => {
        input.addEventListener('change', async (e) => {
          const formId = `update-quantity-form-${input.getAttribute('id').split('-')[1]}`;
          const form = document.getElementById(formId);
          if (!form) return;

          e.preventDefault();
          const formData = new FormData(form);

          try {
            const response = await fetch('services/cart.php', {
              method: 'POST',
              body: formData
            });

            const data = await response.json();

            if (data.status === 'success') {
              await Swal.fire({
                icon: 'success',
                title: data.message,
                showConfirmButton: false,
                timer: 1500
              });
              window.location.reload();
            } else {
              console.error('Error:', data.message);
              Swal.fire({
                icon: 'error',
                title: data.message || 'An error occurred',
                showConfirmButton: false,
                timer: 1500
              });
            }
          } catch (error) {
            console.error('Error:', error);
            Swal.fire({
              icon: 'error',
              title: 'An error occurred',
              showConfirmButton: false,
              timer: 1500
            });
          }
        });
      });
    });
  </script>

  <script>
    // delete cart item
    document.addEventListener('DOMContentLoaded', () => {
      const removeButtons = document.querySelectorAll('button[id^="remove-item-"]');

      removeButtons.forEach(button => {
        button.addEventListener('click', async (e) => {
          e.preventDefault();
          Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
          }).then(async (result) => {
            if (result.isConfirmed) {
              const cartId = button.getAttribute('data-cart-id');
              const formData = new FormData();
              formData.append('action', 'remove_from_cart');
              formData.append('cart_id', cartId);

              try {
                const response = await fetch('services/cart.php', {
                  method: 'POST',
                  body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                  await Swal.fire({
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 1500
                  });
                  window.location.reload();
                } else {
                  console.error('Error:', data.message);
                  Swal.fire({
                    icon: 'error',
                    title: data.message || 'An error occurred',
                    showConfirmButton: false,
                    timer: 1500
                  });
                }
              } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                  icon: 'error',
                  title: 'An error occurred',
                  showConfirmButton: false,
                  timer: 1500
                });
              }
            }
          });
        });
      });
    });
  </script>
</body>

</html>