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

if (!isset($_GET['order_id'])) {
    // redirect back to orders page if no order_id is provided
    header('location:orders.php');
    exit;
}

$order_id = $_GET['order_id'];
$stmt = $conn->prepare(" SELECT 
                o.id AS order_id,
                o.name,
                o.email,
                o.number,
                o.placed_on,
                o.status,
                o.address,
                o.method,
                o.type,
                o.delivery_fee,
                o.receipt,
                o.is_walk_in,
                GROUP_CONCAT(op.product_id) AS product_ids,
                (SELECT SUM(op.subtotal) FROM `order_products` op WHERE op.order_id = o.id) AS total_price
            FROM `orders` o 
            LEFT JOIN `order_products` op ON o.id = op.order_id 
            WHERE o.user_id = :user_id AND o.id = :order_id
            GROUP BY o.id
            ORDER BY o.id");
$stmt->execute([':user_id' => $user_id, ':order_id' => $order_id]);

$order = FormatHelper::formatOrders($stmt->fetchAll(PDO::FETCH_ASSOC), $conn)[0] ?? null;
if (empty($order)) {
    // redirect back to orders page if order not found or does not belong to user
    header('location:orders.php');
    exit;
}

$statusEnumCases = OrderStatusEnum::cases();


$isOrderCompleted = $order && ($order['status']['value'] === OrderStatusEnum::Completed->value && !$order['is_walk_in'] && $order['receipt'] !== '');

?>
<script>

    console.log(<?= json_encode($order) ?>);

</script>

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
    <script>
        console.log(<?= json_encode($order) ?>);
    </script>
    <?php include 'header.php'; ?>

    <main>
        <section class="mt-20">
            <div class="mx-auto max-w-screen-xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8">
                <header class="flex items-center justify-between">
                    <h1 class="text-xl font-bold text-color sm:text-3xl">Order No.
                        <?= htmlspecialchars($order['order_id']) ?>
                    </h1>
                    <div class="flex gap-2">
                        <a href="orders.php"
                            class="inline-block rounded bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring active:bg-gray-400">Back
                            to Orders</a>
                        <?php if ($isOrderCompleted): ?>
                            <a href="receipt.php?order_id=<?= htmlspecialchars($order['order_id']) ?>"
                                class="inline-block rounded bg-color px-4 py-2 text-sm font-medium text-white hover:bg-hover-color focus:outline-none focus:ring active:bg-indigo-600">
                                Invoice</a>

                        <?php endif; ?>

                    </div>

                </header>

                <div class="mt-8">
                    <div class="mb-5">
                        <h2 class="sr-only">Steps</h2>

                        <div>
                            <?php
                            // Filter status cases based on the method
                            $filteredStatusEnumCases = array_filter($statusEnumCases, function ($statusCase) use ($order) {
                                if ($order['method'] === 'cash on delivery' && $statusCase->value === 'pick-up') {
                                    return false;
                                }
                                if ($order['method'] === 'pick up' && $statusCase->value === 'on the way') {
                                    return false;
                                }
                                return true;
                            });

                            // Reindex the filtered cases
                            $filteredStatusEnumCases = array_values($filteredStatusEnumCases);

                            // Find current status index
                            $currentIndex = array_search(
                                $order['status']['value'],
                                array_column($filteredStatusEnumCases, 'value')
                            );

                            // If not found, default to 0
                            $currentIndex = $currentIndex === false ? 0 : $currentIndex;

                            // Total steps
                            $totalSteps = count($filteredStatusEnumCases);

                            // Progress percentage (completed steps ÷ total steps)
                            $progressPercent = (($currentIndex + 1) / $totalSteps) * 100;
                            ?>

                            <!-- Progress Bar -->
                            <div class="overflow-hidden rounded-full bg-gray-200">
                                <div class="h-2 rounded-full bg-blue-500" style="width: <?= $progressPercent ?>%;">
                                </div>
                            </div>

                            <!-- Step Labels -->
                            <ol class="mt-4 grid grid-cols-<?= $totalSteps ?> text-sm font-medium text-gray-500">
                                <?php foreach ($filteredStatusEnumCases as $index => $statusCase):
                                    $isCompleted = $index <= $currentIndex;
                                    ?>
                                    <li
                                        class="flex items-center justify-start sm:gap-1.5 <?= $isCompleted ? 'text-blue-600' : 'text-gray-500' ?>">
                                        <span class="hidden sm:inline">
                                            <?= htmlspecialchars($statusCase->label()) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>


                    <div class="overflow-hidden rounded-lg border border-gray-200 shadow-md shadow-gray-300/10">
                        <div class="bg-gray-50 px-6 py-4">
                            <h2 class="text-lg font-medium text-gray-900">Order Details</h2>
                            <h4>
                                Name: <?= htmlspecialchars($order['name']) ?> (<?= htmlspecialchars($order['email']) ?>)
                            </h4>
                            <p class="mt-1 text-sm text-gray-600">
                                Contact No. : <?= htmlspecialchars($order['number'] ?? 'N/A') ?>
                            </p>
                            <p class="mt-1 text-sm text-gray-600">Placed on:
                                <?= htmlspecialchars($order['placed_on']) ?>
                            </p>
                            <p class="mt-1 text-sm text-gray-600">Payment Method:
                                <?= htmlspecialchars(ucwords($order['method'])) ?>
                            </p>
                            <p class="mt-1 text-sm text-gray-600">Status:
                                <span
                                    class="inline-block rounded-full px-3 py-1 text-sm font-semibold text-white bg-[<?= $order['status']['color'] ?>]">
                                    <?= $order['status']['label'] ?>
                                </span>
                            </p>

                            <p class="mt-1 text-sm text-gray-600">Address:
                                <?= htmlspecialchars($order['address'] ?? 'N/A') ?>
                            </p>

                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Product</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Price</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Quantity</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($order['products'] as $product): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= $product['name'] ?>
                                            </div>
                                            <?php if ($order['type'] === 'coffee' && !empty($product['ingredients'])): ?>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    <?php foreach ($product['ingredients'] as $ingredient): ?>
                                                        <div>
                                                            <?= htmlspecialchars($ingredient['name']) ?>:
                                                            <?= htmlspecialchars($ingredient['level']) ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($order['type'] === 'coffee' && !empty($product['cup_sizes'])): ?>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Cup Sizes:
                                                    <?php foreach ($product['cup_sizes'] as $size => $count): ?>
                                                        <?php if ($count > 0): ?>
                                                            <span>
                                                                <?= htmlspecialchars(ucfirst($size)) ?>
                                                                (<?= htmlspecialchars($count) ?>)
                                                            </span>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($order['type'] === 'coffee' && !empty($product['add_ons'])): ?>
                                                <div class="text-sm text-gray-500 mt-1">
                                                    Add-Ons:
                                                    <?php foreach ($product['add_ons'] as $addOn): ?>
                                                        <span>
                                                            <?= htmlspecialchars($addOn['name']) ?>
                                                            (₱ <?= number_format($addOn['price'], 2) ?>)
                                                        </span>
                                                    <?php endforeach; ?>
                                                </div>

                                            <?php endif; ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ₱ <?= number_format($product['price'], 2) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?= $product['quantity'] ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            ₱ <?= $product['subtotal'] ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>

                                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900">
                                        <?php if ($order['method'] !== 'pick up'): ?>
                                            <span class="block">
                                                Delivery Fee:
                                            </span>
                                        <?php endif; ?>
                                        <span>
                                            Total:
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-900">
                                        <?php $total = $order['total_price'] ?>
                                        <?php if ($order['method'] !== 'pick up'): ?>
                                            <span class="block">
                                                ₱ <?= $order['delivery_fee'] ?>
                                            </span>
                                            <?php $total += (float) str_replace(',', '', $order['delivery_fee']) ?>
                                        <?php endif; ?>

                                        <span>
                                            ₱ <?= number_format($total, 2) ?>
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </main>



    <?php include 'footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>

</body>

</html>