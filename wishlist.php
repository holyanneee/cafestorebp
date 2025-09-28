<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
session_start();

if (empty($_SESSION['user_id'])) {
   header('Location: login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

/** 
 * This array will hold success/error/info messages for later display in the UI 
 */
$alert = [];

if (!empty($_GET['action'])) {
   $action = $_GET['action'];
   $wishlist_id = $_GET['wishlist_id'] ?? null;

   if ($wishlist_id) {
      $stmt = $conn->prepare("SELECT id FROM wishlist WHERE id = ? AND user_id = ?");
      $stmt->execute([$wishlist_id, $user_id]);
      if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
         $alert[] = ['type' => 'error', 'message' => 'Wishlist item not found'];
         header('Location: wishlist.php');
         exit;
      }
   }

   switch ($action) {
      case 'add':
         $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE id = ? AND user_id = ?");
         $stmt->execute([$wishlist_id, $user_id]);
         $item = $stmt->fetch(PDO::FETCH_ASSOC);
         if ($item) {
            addToCart($conn, $wishlist_id, $user_id, $item['product_id']);
            $alert[] = ['type' => 'success', 'message' => 'Item added to cart'];
         }
         header('Location: cart.php');
         exit;

      case 'remove':
         $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
         $stmt->execute([$wishlist_id, $user_id]);
         $alert[] = ['type' => 'success', 'message' => 'Item removed from wishlist'];
         header('Location: wishlist.php');
         exit;

      case 'add-all':
         $stmt = $conn->prepare("SELECT id, product_id FROM wishlist WHERE user_id = ?");
         $stmt->execute([$user_id]);
         $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

         foreach ($items as $item) {
            addToCart($conn, $item['id'], $user_id, $item['product_id']);
         }

         $alert[] = ['type' => 'success', 'message' => 'All wishlist items moved to cart'];
         header('Location: cart.php');
         exit;

      default:
         header('Location: wishlist.php');
         exit;
   }
}

function addToCart(PDO $conn, $wishlist_id, $user_id, $product_id): void
{
   $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
   $stmt->execute([$user_id, $product_id]);
   $existing = $stmt->fetch(PDO::FETCH_ASSOC);

   $select_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
   $select_product->execute([$product_id]);
   $product = $select_product->fetch(PDO::FETCH_ASSOC);

   if (!$product)
      return;

   if ($existing) {
      $newQty = $existing['quantity'] + 1;
      $subtotal = $product['price'] * $newQty;
      $conn->prepare("UPDATE cart SET quantity = ?, subtotal = ? WHERE id = ?")
         ->execute([$newQty, $subtotal, $existing['id']]);
   } else {
      if ($product['type'] === 'coffee') {
         $ingredients = [];
         $ingredient_ids = json_decode($product['ingredients'] ?? '[]', true);
         if (!empty($ingredient_ids)) {
            $inQuery = implode(',', array_fill(0, count($ingredient_ids), '?'));
            $ingredient_stmt = $conn->prepare("SELECT id,name FROM ingredients WHERE id IN ($inQuery)");
            $ingredient_stmt->execute($ingredient_ids);
            while ($ingredient = $ingredient_stmt->fetch(PDO::FETCH_ASSOC)) {
               $ingredients[$ingredient['id']] = [
                  'name' => $ingredient['name'],
                  'level' => 'Regular'
               ];
            }
         }

         $cup_sizes = json_decode($product['cup_sizes'] ?? '{}', true);
         $cup_size = isset($cup_sizes['regular']) ? 'Regular' : 'Small';
         $cup_price = $cup_sizes[strtolower($cup_size)] ?? 0;

         $conn->prepare("
                INSERT INTO cart (user_id, product_id, quantity, ingredients, cup_size, subtotal, type) 
                VALUES (?, ?, 1, ?, ?, ?, 'coffee')
            ")->execute([
                  $user_id,
                  $product_id,
                  json_encode($ingredients),
                  json_encode(['size' => $cup_size, 'price' => $cup_price]),
                  ($product['price'] + $cup_price)
               ]);
      } else {
         $conn->prepare("
                INSERT INTO cart (user_id, product_id, quantity, subtotal, type) 
                VALUES (?, ?, 1, ?, 'religious')
            ")->execute([$user_id, $product_id, $product['price']]);
      }
   }

   $conn->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?")->execute([$wishlist_id, $user_id]);
}

$sql = "
    SELECT 
        w.id AS wishlist_id,
        w.product_id,
        p.name,
        p.price,
        p.details,
        p.image
    FROM wishlist w
    INNER JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.id DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_price = array_sum(array_column($wishlist_items, 'price'));
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
         <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <header>
               <h1 class="text-xl font-bold text-color sm:text-3xl">Your Wishlist</h1>
            </header>

            <div class="mt-8">
               <ul class="space-y-4">
                  <?php if (!empty($wishlist_items)): ?>
                     <?php
                     foreach ($wishlist_items as $item):

                        ?>
                        <li class="flex items-center gap-4 pb-4">
                           <img src="uploaded_img/<?= htmlspecialchars($item['image']); ?>"
                              alt="<?= htmlspecialchars($item['name']); ?>" class="size-20 rounded-sm object-cover" />

                           <div class="flex-1">
                              <h3 class="text-sm text-gray-900 font-semibold"><?= htmlspecialchars($item['name']); ?></h3>

                              <dl class="mt-1 space-y-px text-[11px] text-gray-600">
                                 <div>
                                    <dt class="inline">Price:</dt>
                                    <dd class="inline">₱<?= number_format($item['price'], 2); ?></dd>
                                 </div>
                                 <div>
                                    <dt class="inline">Description:</dt>
                                    <dd class="inline">
                                       <?= htmlspecialchars(strlen($item['details']) > 50 ? substr($item['details'], 0, 50) . '...' : $item['details']); ?>
                                    </dd>
                                 </div>
                              </dl>
                           </div>

                           <div class="flex items-center gap-2">

                              <a href="wishlist.php?action=add&wishlist_id=<?= $item['wishlist_id']; ?>"
                                 class="flex items-center gap-2 text-gray-600 transition hover:text-green-600">
                                 <span class=" sr-only">Add to cart</span>
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                 </svg>
                                 <span class="text-sm">
                                    Add to cart
                                 </span>
                              </a>

                              <div class="h-6 w-px bg-gray-300"></div>

                              <a href="wishlist.php?action=remove&wishlist_id=<?= $item['wishlist_id']; ?>"
                                 class="flex items-center gap-2 text-gray-600 transition hover:text-red-600">
                                 <span class=" sr-only">Remove item</span>
                                 <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166
                         m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084
                         a2.25 2.25 0 01-2.244-2.077L4.772 5.79
                         m14.456 0a48.108 48.108 0 00-3.478-.397" />
                                 </svg>
                                 <span class="text-sm">
                                    Remove
                                 </span>

                              </a>
                           </div>
                        </li>

                     <?php endforeach; ?>
                  <?php else: ?>
                     <li class="text-center text-gray-500 py-6">Your wishlist is empty.</li>
                  <?php endif; ?>
               </ul> <!-- Cart Total -->
               <div class="mt-8 flex justify-end border-t border-gray-100 pt-8">
                  <div class="w-screen max-w-lg space-y-4">
                     <dl class="space-y-0.5 text-sm text-gray-700">
                        <div class="flex justify-between !text-base font-medium">
                           <dt>Total</dt>
                           <dd>₱
                              <?= isset($total_price) ? number_format($total_price, 2) : '0.00'; ?>
                           </dd>
                        </div>
                     </dl>

                     <div class="flex justify-end">
                        <a href="wishlist.php?action=add-all"
                           class="block rounded-sm bg-gray-700 px-5 py-3 text-sm text-gray-100 transition hover:bg-gray-600">
                           Add All to Cart
                        </a>
                     </div>
                  </div>
               </div>

            </div>
         </div>
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
</body>

</html>