<?php
@include 'config.php';
session_start();



$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    header('location:admin_orders.php');
    exit();
}

// Fetch order details
$select_orders = $conn->prepare("
    SELECT 
        o.id AS order_id,
        o.name,
        o.email,
        o.placed_on,
        o.status,
        o.type,
        o.method,
        o.address,
        o.updated_by_cashier,
        o.updated_by_barista,
        o.number,
        o.receipt,

        GROUP_CONCAT(op.product_id) AS product_ids,
        (SELECT SUM(op2.subtotal) FROM order_products op2 WHERE op2.order_id = o.id) AS total_price
    FROM orders o 
    LEFT JOIN order_products op ON o.id = op.order_id
    WHERE o.id = ?
    GROUP BY o.id
");
$select_orders->execute([$order_id]);
$order = $select_orders->fetch(PDO::FETCH_ASSOC);

function calculateReceiptHeight($products_data)
{
    $line_height = 4;
    $addon_height = 4;

    $height = 40; // header + footer + gaps

    foreach ($products_data as $product) {
        $height += $line_height; // product line

        if (!empty($product['add_ons'])) {
            $height += count($product['add_ons']) * $addon_height;
        }
    }

    return max($height, 100); // prevent too small height
}

if (!$order) {
    die('Order not found!');
}

if (empty($order['receipt'])) {
    $admin_id = $_SESSION['cashier_id'] ?? null;
    if (!$admin_id) {
        header('location:login.php');
        exit();
    }
    $cashier_name = $_SESSION['cashier_name'] ?? 'Unknown';

    $product_ids = explode(',', $order['product_ids']);
    $products_data = [];

    foreach ($product_ids as $product_id) {
        $select_order_products = $conn->prepare("
            SELECT op.*, p.name AS product_name, p.price AS product_price 
            FROM `order_products` op 
            LEFT JOIN `products` p ON p.id = op.product_id 
            WHERE op.order_id = ? AND op.product_id = ?
        ");
        $select_order_products->bindParam(1, $order_id, PDO::PARAM_INT);
        $select_order_products->bindParam(2, $product_id, PDO::PARAM_INT);
        $select_order_products->execute();
        $order_product = $select_order_products->fetch(PDO::FETCH_ASSOC);


        $quantity = intval($order_product['quantity']);
        $base_price = floatval($order_product['product_price']);

        // Decode cup size
        $cup_data = json_decode($order_product['cup_sizes'], true);
        $cup = $cup_data['size'] ?? '';
        $cup_price = floatval($cup_data['price'] ?? 0);

        $ingredients = $order_product['ingredients'] ? json_decode($order_product['ingredients'], true) : [];
        $add_ons = $order_product['add_ons'] ? json_decode($order_product['add_ons'], true) : [];

        $products_data[] = [
            'name' => $order_product['product_name'] . ($cup ? " ({$cup})" : ''),
            'price' => floatval($order_product['subtotal']) / max(intval($order_product['quantity']), 1),
            'qty' => intval($order_product['quantity']),
            'ingredients' => $ingredients,
            'add_ons' => $add_ons,
        ];

    }


    // Generate PDF
    require('fpdf/fpdf.php');



    $total_height = calculateReceiptHeight($products_data);

    $pdf = new FPDF('P', 'mm', array(100, $total_height));

    $pdf->AddPage();


    $pdf->SetFont('Arial', 'B', 12);
    if ($order['type'] == 'coffee') {
        $pdf->Cell(0, 5, 'Kape Milagrosa', 0, 1, 'C');
    } else {
        $pdf->Cell(0, 5, 'Anak ng Birhen', 0, 1, 'C');

    }
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 4, 'Purok 3, 0356 Chico St, Calauan, 4012 Laguna', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Phone: 09058774385', 0, 1, 'C');
    $pdf->Ln(3);

    $pdf->Cell(0, 4, "Cashier: $cashier_name", 0, 1, 'L');
    $pdf->Cell(0, 4, "Date: " . date('d-M-Y H:i'), 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('Courier', '', 9);
    $total_price = 0;
    foreach ($products_data as $product) {
        $total_price += $product['price'] * $product['qty'];

        $line = str_pad($product['name'], 25);
        $line .= str_pad('x' . $product['qty'], 5, ' ', STR_PAD_LEFT);
        $line .= str_pad('Php ' . number_format($product['price'], 2), 12, ' ', STR_PAD_LEFT);
        $pdf->Cell(0, 4, $line, 0, 1);

        // Add-ons
        if (!empty($product['add_ons'])) {
            foreach ($product['add_ons'] as $addon) {

                $priceFormatted = number_format($addon['price'], 2);
                $pdf->Cell(0, 4, "   {$addon['name']} (+{$priceFormatted})", 0, 1);
            }
            $pdf->SetFont('Courier', '', 9);
        }

    }


    $pdf->Ln(2);
    $pdf->Cell(0, 0, str_repeat('-', 40), 0, 1);
    $pdf->Ln(1);
    $pdf->SetFont('Courier', 'B', 9);
    $line = str_pad('TOTAL:', 20);
    $line .= str_pad('Php ' . number_format($total_price, 2), 20, ' ', STR_PAD_LEFT);
    $pdf->Cell(0, 5, $line, 0, 1);

    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 4, 'Thank you for your purchase!', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Please visit us again.', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Inquiries: 09058774385', 0, 1, 'C');
    $pdf->SetAutoPageBreak(true, 5);
    $receipts_dir = 'receipts/';
    if (!is_dir($receipts_dir)) {
        mkdir($receipts_dir, 0755, true);
    }

    $unique_id = uniqid();
    $pdf_filename = $receipts_dir . 'receipt_' . $admin_id . '_' . date('Ymd_His') . '_' . $unique_id . '.pdf';
    $pdf->Output('F', $pdf_filename);

    $db_filename = basename($pdf_filename);
    $update = $conn->prepare("UPDATE orders SET receipt = ? WHERE id = ?");
    $update->execute([$db_filename, $order_id]);

    $receipt_file = $pdf_filename;
} else {
    $receipt_file = 'receipts/' . $order['receipt'];
}

if (!file_exists($receipt_file)) {
    header('location:cashier_order_error.php');
    exit();
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($receipt_file) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
@readfile($receipt_file);
exit();
?>