<?php

@include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Details</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <!-- custom css file link  -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* remove a tag text design */
        a {
            text-decoration: none;
            color: inherit;
        }

        .gradient-custom-2 {
            /* fallback for old browsers */
            background: #a1c4fd;

            /* Chrome 10-25, Safari 5.1-6 */
            background: -webkit-linear-gradient(to right, rgba(161, 196, 253, 1), rgba(194, 233, 251, 1));

            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            background: linear-gradient(to right, rgba(161, 196, 253, 1), rgba(194, 233, 251, 1))
        }

        #progressbar-1 {
            color: #455A64;
        }

        #progressbar-1 li {
            list-style-type: none;
            font-size: 13px;
            width: 33.33%;
            float: left;
            position: relative;
        }

        #progressbar-1 #step1:before {
            content: "1";
            color: #fff;
            width: 29px;
            margin-left: 22px;
            padding-left: 11px;
        }

        #progressbar-1 #step2:before {
            content: "2";
            color: #fff;
            width: 29px;
        }

        #progressbar-1 #step3:before {
            content: "3";
            color: #fff;
            width: 29px;
            margin-right: 22px;
            text-align: center;
        }

        #progressbar-1 li:before {
            line-height: 29px;
            display: block;
            font-size: 12px;
            background: #455A64;
            border-radius: 50%;
            margin: auto;
        }

        #progressbar-1 li:after {
            content: '';
            width: 121%;
            height: 2px;
            background: #455A64;
            position: absolute;
            left: 0%;
            right: 0%;
            top: 15px;
            z-index: -1;
        }

        #progressbar-1 li:nth-child(2):after {
            left: 50%
        }

        #progressbar-1 li:nth-child(1):after {
            left: 25%;
            width: 121%
        }

        #progressbar-1 li:nth-child(3):after {
            left: 25%;
            width: 50%;
        }

        #progressbar-1 li.active:before,
        #progressbar-1 li.active:after {
            background: #1266f1;
        }

        .card-stepper {
            z-index: 0
        }
    </style>
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh;">

    <?php include 'header.php'; ?>

    <main style="flex: 1;">
        <div class="container">
            <?php
            // get the order_id
            if (isset($_GET['order_id']) && !empty($_GET['order_id'])) {
                $order_id = $_GET['order_id'];
            } else {
                echo "<script>
                        alert('Order ID not found!');
                        window.location.href = 'orders.php';
                    </script>";
                exit;
            }
            // Fetch order details from the database
            $order_select = $conn->prepare("
            SELECT 
                o.id AS order_id,
                o.name,
                o.email,
                o.placed_on,
                o.payment_status,
                o.type,
                o.number,
                o.method,
                o.number,
                o.updated_at,
                GROUP_CONCAT(op.product_id) AS product_ids,
                (SELECT SUM(op2.subtotal) FROM `order_products` op2 WHERE op2.order_id = o.id) AS total_price
                FROM `orders` o 
                LEFT JOIN `order_products` op ON o.id = op.order_id
                WHERE o.id = ?
                GROUP BY o.id
                ORDER BY o.id
            ");
            $order_select->execute([$order_id]);


            if ($order_select->rowCount() > 0) {
                $order = $order_select->fetch(PDO::FETCH_ASSOC);

                $formatted_order = [
                    'order_id' => $order['order_id'],
                    'name' => htmlspecialchars($order['name']),
                    'email' => htmlspecialchars($order['email']),
                    'placed_on' => date('M d, Y', strtotime($order['placed_on'])),
                    'method' => htmlspecialchars($order['method']),
                    'number' => htmlspecialchars($order['number']),
                    'placed_on' => date('M d, Y', strtotime($order['placed_on'])),
                    'payment_status' => htmlspecialchars($order['payment_status']),
                    'total_price' => number_format($order['total_price'], 2),
                    'total_quantity' => array_sum(array_map(function ($product_id) use ($conn, $order) {
                        $select_order_product = $conn->prepare("SELECT quantity FROM `order_products` WHERE order_id = ? AND product_id = ?");
                        $select_order_product->execute([$order['order_id'], $product_id]);
                        $order_product = $select_order_product->fetch(PDO::FETCH_ASSOC);
                        return $order_product ? (int) $order_product['quantity'] : 0;
                    }, explode(',', $order['product_ids']))),
                    'updated_at' => date('M d, Y, h:i A', strtotime($order['updated_at'])),
                    'products' => array_filter(array_map(function ($product_id) use ($conn, $order) {
                        // Fetch product details
                        $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                        $select_product->execute([$product_id]);
                        $product = $select_product->fetch(PDO::FETCH_ASSOC);
                        // Check if product exists
                        if (!$product) {
                            return null; // Skip if product not found
                        }

                        // get the quntity and subtotal from order_products
                        $select_order_product = $conn->prepare("SELECT * FROM `order_products` WHERE order_id = ? AND product_id = ?");
                        $select_order_product->execute([$order['order_id'], $product_id]);
                        $order_product = $select_order_product->fetch(PDO::FETCH_ASSOC);
                        if (!$order_product) {
                            return null; // Skip if order product not found
                        }
                        $product['quantity'] = $order_product['quantity'];
                        $product['subtotal'] = $order_product['subtotal'];

                        return [
                            'id' => $product['id'],
                            'image' => htmlspecialchars($product['image'] ?? '', ENT_QUOTES, 'UTF-8'),
                            'name' => htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                            'quantity' => htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'),
                            'price' => number_format($product['price'] ?? 0, 2),
                            'subtotal' => number_format($product['subtotal'] ?? 0, 2),
                            'cup_size' => json_decode($order_product['cup_sizes'] ?? '[]', true)['size'] ?? '',
                            'cup_size_price' => number_format(json_decode($order_product['cup_sizes'] ?? '[]', true)['price'] ?? 0, 2),
                            'ingredients' => array_map(function ($ingredient) {
                            return [
                                'name' => htmlspecialchars($ingredient['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                                'level' => htmlspecialchars($ingredient['level'] ?? '', ENT_QUOTES, 'UTF-8')
                            ];
                        }, json_decode($order_product['ingredients'] ?? '[]', true) ?: []),
                            'add_ons' => array_map(function ($addOn) {
                            return [
                                'name' => htmlspecialchars($addOn['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                                'price' => number_format($addOn['price'] ?? 0, 2)
                            ];
                        }, json_decode($order_product['add_ons'] ?? '[]', true) ?: []),
                        ];
                    }, explode(',', $order['product_ids'])))
                ];

                $order = $formatted_order;
            } else {
                echo "<script>
                        alert('Order not found!');
                        window.location.href = 'orders.php';
                    </script>";
                exit;
            }

            ?>
            <div class="row mt-5">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    <div class="col-md-10 col-lg-8 col-xl-6">
                        <div class="card card-stepper mb-5" style="border-radius: 16px;">
                            <div class="card-header p-4 bg-success text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-2 text-white">
                                            <span class="text-white">
                                                Order ID:
                                            </span>
                                            <?= $order['order_id'] ?>
                                        </h4>

                                    </div>
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Place On
                                            </span>
                                            <?= $order['placed_on'] ?>
                                        </h4>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Name:
                                            </span>
                                            <?= $order['name'] ?>
                                        </h4>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Email:
                                            </span>
                                            <?= $order['email'] ?>
                                        </h4>

                                    </div>
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Number:
                                            </span>
                                            <?= $order['number'] ?>
                                        </h4>

                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Total Price:
                                            </span>
                                            ₱<?= $order['total_price'] ?>
                                        </h4>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Total Quantity:
                                            </span>
                                            <?= $order['total_quantity'] ?>
                                        </h4>
                                    </div>

                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                MoP:
                                            </span>
                                            <?= $order['method'] ?>
                                        </h4>
                                    </div>
                                    <div>
                                        <h4 class="mb-0 text-white">
                                            <span class="fw-bold text-white">
                                                Status:
                                            </span>
                                            <?php if ($order['payment_status'] == 'On Queue'): ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php elseif ($order['payment_status'] == 'On Going'): ?>
                                                <span class="badge bg-info text-dark">On Going</span>
                                            <?php elseif ($order['payment_status'] == 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </h4>
                                    </div>


                                </div>
                                <!-- <ul id="progressbar-1" class="mx-0 mt-0 mb-5 px-0 pt-0 pb-4 mt-3">
                                    <li class="step0 active" id="step1">
                                        <span style="margin-left: 22px; margin-top: 12px;">PLACED</span>
                                    </li>
                                    <li class="step0 active text-center" id="step2">
                                        <span>SHIPPED</span>
                                    </li>
                                    <li class="step0 text-muted text-end" id="step3">
                                        <span style="margin-right: 22px;">DELIVERED</span>
                                    </li>
                                </ul> -->
                            </div>
                            <div class="card-body p-4">
                                <?php foreach ($order['products'] as $product): ?>
                                    <div class="d-flex flex-row mb-4 pb-2">
                                        <div class="flex-fill">
                                            <h3 class="bold"><?= $product['name'] ?> (₱<?= $product['price'] ?>)</h3>
                                            <h4> Qt: <?= $product['quantity'] ?> item</h4>
                                            <h4> Cup Size: <?= $product['cup_size'] ?> (₱<?= $product['cup_size_price'] ?>)
                                            </h4>
                                            <h4>
                                                Ingredients:
                                                <?php if (!empty($product['ingredients'])): ?>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($product['ingredients'] as $ingredient): ?>
                                                            <li>
                                                                <?= htmlspecialchars($ingredient['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                (<?= htmlspecialchars($ingredient['level'], ENT_QUOTES, 'UTF-8') ?>)
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </h4>
                                            <h4>
                                                Add-ons:
                                                <?php if (!empty($product['add_ons'])): ?>
                                                    <ul class="list-unstyled">
                                                        <?php foreach ($product['add_ons'] as $addOn): ?>
                                                            <li>
                                                                <?= htmlspecialchars($addOn['name'], ENT_QUOTES, 'UTF-8') ?>
                                                                (₱<?= htmlspecialchars($addOn['price'], ENT_QUOTES, 'UTF-8') ?>)
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </h4>
                                            <h4>Total: ₱<?= $product['subtotal'] ?></h4>
                                        </div>
                                        <div>
                                            <img class="align-self-center img-fluid"
                                                src="uploaded_img/<?= $product['image'] ?>" width="100">
                                        </div>
                                    </div>
                                    <?php ; endforeach; ?>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>



    <?php include 'footer.php'; ?>

    <script src="js/script.js"></script>

    <!-- bootstrap js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>