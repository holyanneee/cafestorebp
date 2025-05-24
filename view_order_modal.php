<!-- modal -->
 <style>
    .modal-product-name {
        font-size: 1.5rem !important;
        font-weight: bold;
    }
    .modal-products{
        font-size: 1.2rem !important;
    }
    .modal-products span {
        font-size: 1.2rem !important;
    }
 </style>
<div class="modal fade" id="viewOrderModal-<?= $order['order_id'] ?>" tabindex="-1"
    aria-labelledby="viewOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="viewOrderModalLabel">
                    <i class="fas fa-shopping-cart text-white"></i> Order Details
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
            <h1 class="fw-bold"> Order ID: #<?= $order['order_id'] ?></h1>
                <div class="row">
                    <div class="col-md-6">
                        <div class="order-details mb-2">
                            <strong>Customer Name:</strong>
                            <?= htmlspecialchars($order['name']) ?>
                        </div>
                        <div class="order-details mb-2">
                            <strong>Email:</strong>
                            <?= htmlspecialchars($order['email']) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="order-details mb-2">
                            <strong>Order Date:</strong>
                            <?= $order_date ?>
                        </div>
                        <div class="order-details mb-2">
                            <strong>Status:</strong>
                            <span
                                class="status-badge <?= $status_class ?>"><?= ucfirst($order['payment_status']) ?></span>
                        </div>
                    </div>
                </div>
                <div class="order-details mb-3">
                    <strong>Total Amount:</strong>
                    ₱<?= number_format((float) str_replace(',', '', $order['total_price']), 2) ?>
                </div>
                <h1 class="fw-bold">Order Information:</h1>
                
                <?php foreach ($order['products'] as $product): ?>
                    <div>
                        <strong class="modal-product-name">
                            <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['cup_sizes']['size']) ?>) x<?= $product['quantity'] ?>
                        </strong>
                        <div class="modal-products text-muted small my-2">
                            Product: ₱<?= number_format($product['price'], 2) ?></br>
                            Cup Size: ₱<?= number_format($product['cup_sizes']['price'], 2) ?></br>
                            Quantity:<?= $product['quantity'] ?>
                        </div>
                        <?php if (!empty($product['ingredients'])): ?>
                            <span class="h5">Ingredients:</span>
                            <div class="modal-products text-muted small">
                                    <?php foreach ($product['ingredients'] as $ingredient): ?>
                                        <?= htmlspecialchars($ingredient['name']) ?> : <?= htmlspecialchars($ingredient['level'])?> </br>
                                    <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['add_ons'])): ?>
                            <span class="h5">Add-ons:</span>
                            <div class="modal-products text-muted small">
                                <?= implode(', ', array_map(function ($addOn) {
                                    return htmlspecialchars($addOn['name']) . " (+₱" . number_format($addOn['price'], 2) . ")";
                                }, $product['add_ons'])) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['specialInstructions'])): ?>
                            <div class="modal-products text-muted small">
                                Note: <?= htmlspecialchars($product['specialInstructions']) ?>
                            </div>
                        <?php endif; ?>
                        <!-- total -->
                        <div class="modal-products text-muted small">
                            <strong>Total:</strong> ₱<?= number_format(($product['price'] + $product['cup_sizes']['price']) * $product['quantity'] + array_sum(array_column($product['add_ons'], 'price')) * $product['quantity'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>