<?php


@include 'config.php';
session_start();
require_once 'helpers/FormatHelper.php';
require_once 'enums/OrderStatusEnum.php';
use Helpers\FormatHelper;
use Enums\OrderStatusEnum;

if (!isset($_SESSION['user_id'])) {
   header('location:login.php');
   exit;
}

$user_id = $_SESSION['user_id'];

$alert = $_SESSION['alert'] ?? [];
unset($_SESSION['alert']);

$type = isset($_GET['type']) && in_array($_GET['type'], ['coffee', 'religious']) ? $_GET['type'] : '';
$search = trim($_GET['search'] ?? '');

$where = 'WHERE user_id = :user_id';
if ($search !== '') {
   $where .= " AND name LIKE :search";
   $params[':search'] = "%$search%";
}
if ($type !== '') {
   $where .= " AND type = :type";
   $params[':type'] = $type;
}

$params[':user_id'] = $user_id;
$stmt = $conn->prepare(" SELECT 
                o.id AS order_id,
                o.name,
                o.email,
                o.placed_on,
                o.status,
                o.address,
                o.type,
                o.receipt,
                o.method,
                o.number,
                o.delivery_fee,
                o.is_walk_in,
                GROUP_CONCAT(op.product_id) AS product_ids,
                (SELECT SUM(op.subtotal) FROM `order_products` op WHERE op.order_id = o.id) AS total_price
            FROM `orders` o 
            LEFT JOIN `order_products` op ON o.id = op.order_id $where GROUP BY o.id
            ORDER BY o.id");
$stmt->execute($params);


$orders = FormatHelper::formatOrders($stmt->fetchAll(PDO::FETCH_ASSOC), $conn);

?>



<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Orders | Kape Milagrosa
   </title>
   <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

   <link rel="stylesheet" href="css/new_style.css">
</head>

<body>
   <?php include 'header.php'; ?>

   <main>
      <section class="mt-20 <?= count($orders) < 3 ? 'min-h-[60vh]' : ''; ?>">
         <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
            <header class="flex items-center justify-between">
               <h1 class="text-xl font-bold text-color sm:text-3xl">Your Orders</h1>
               <form method="get" class="flex flex-wrap gap-3" id="filters">
                  <!-- Type Filter -->
                  <select name="type"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5"
                     id="type">
                     <option value="">All Types</option>
                     <option value="coffee" <?= $type === 'coffee' ? 'selected' : ''; ?>>Coffee</option>
                     <option value="religious" <?= $type === 'religious' ? 'selected' : ''; ?>>Religious Items
                     </option>
                  </select>

                  <!-- Search -->
                  <input type="text" name="search" id="serach" placeholder="Search products..."
                     value="<?= htmlspecialchars($search); ?>"
                     class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5" />
                  <!-- reset btn -->
                  <a href="products.php"
                     class="h-10 rounded bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200">Reset</a>
               </form>
            </header>

            <div class="mt-8">
               <ul class="space-y-4">
                  <?php if (!empty($orders)): ?>
                     <?php
                     foreach ($orders as $order):
                        $isOrderCompleted = $order && ($order['status']['value'] === OrderStatusEnum::Completed->value && $order['receipt'] !== '');
                        ?>
                        <li class="flex items-center gap-4 pb-4">

                           <div class="flex-1">
                              <h3 class="text-sm text-gray-900 font-semibold">
                                 <?= htmlspecialchars($order['name']); ?>
                              </h3>

                              <dl class="mt-1 space-y-px text-[11px] text-gray-600">
                                 <div>
                                    <dt class="inline">Total Price:</dt>
                                    <dd class="inline">₱<?= number_format((float) str_replace(',', '', $order['total_price']), 2) ?></dd>
                                 </div>
                                 <script>
                                    console.log(<?= json_encode($order); ?>);
                                 </script>
                                 <div>
                                    <dt class="inline">Date:</dt>
                                    <dd class="inline">
                                       <?= htmlspecialchars(date('F d, Y', strtotime($order['placed_on']))); ?>
                                    </dd>
                                 </div>
                                 <div class="mt-1">
                                    <dt class="inline">Status:</dt>
                                    <dd class="inline">
                                       <span
                                          class="rounded-full px-2 py-1 text-xs font-semibold <?= $order['status']['color']; ?> ">
                                          <span
                                             class="rounded-full bg-[<?= $order['status']['color']; ?>] px-2 py-0.5 text-sm whitespace-nowrap text-white">
                                             <?= $order['status']['label']; ?>
                                          </span>
                                       </span>
                                    </dd>
                                 </div>
                              </dl>
                           </div>
                           <div class="flex items-center gap-2">
                              <!-- recipt -->
                              <?php if ($order['status']['value'] === 'completed' && $isOrderCompleted): ?>
                                 <a href="receipt.php?order_id=<?= $order['order_id']; ?>" target="_blank"
                                    class="rounded bg-green-100 px-4 py-2 text-sm text-green-700 hover:bg-green-200">Invoice</a>
                              <?php endif; ?>
                              <!-- view -->
                              <a href="view_order.php?order_id=<?= $order['order_id']; ?>"
                                 class="rounded bg-blue-100 px-4 py-2 text-sm text-blue-700 hover:bg-blue-200">View</a>
                              </a>
                           </div>
                        </li>

                     <?php endforeach; ?>
                  <?php else: ?>
                     <li class="text-center text-gray-500 py-6">
                        No orders found.
                     </li>
                  <?php endif; ?>
               </ul>
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
   <script>
      // check if the filters are changed
      document.addEventListener('DOMContentLoaded', () => {
         const filterForm = document.getElementById('filters');

         const typeSelect = document.getElementById('type');
         const searchInput = document.getElementById('serach');

         const initialType = typeSelect.value;
         const initialSearch = searchInput.value;

         function checkFilters() {
            if (typeSelect.value !== initialType || sortSelect.value !== initialSort || searchInput.value !== initialSearch) {
               // Submit the form
               filterForm.submit();
            }
         }

         typeSelect.addEventListener('change', checkFilters);
         searchInput.addEventListener('input', checkFilters);
      });
   </script>
</body>

</html>