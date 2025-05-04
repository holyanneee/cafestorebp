<?php
@include 'config.php';
session_start();

// Ensure the user is logged in as an admin or cashier
$admin_id = $_SESSION['cashier_id'] ?? null;
if (!$admin_id) {
    header('location:login.php');
    exit();
}

// Get the order_id from the URL
$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('location:admin_orders.php');
    exit();
}

// Fetch the order data from the database
$stmt = $conn->prepare("SELECT * FROM `orders` WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the order exists
if (!$order) {
    die('Order not found!');
}

// Check if the receipt field is empty or null
if (empty($order['receipt'])) {
    $cashier_name = $_SESSION['cashier_name'] ?? 'Unknown';
    $cashier_id = $_SESSION['cashier_id'] ?? null;

    if (!$cashier_id) {
        header('location:login.php');
        exit;
    }

    // Get the product data from the order
    $total_products = explode(', ', $order['total_products']); // Example: ["yaya (1)", "yaya (1)"]

    // Array to hold aggregated product data
    $products_data = [];

    // Loop through and aggregate product quantities
    foreach ($total_products as $item) {
        // Handle product name and quantity extraction, even with spacing issues
        if (preg_match('/(.*)\s?\(\s*(\d+)\s*\)/', trim($item), $matches)) { 
            $product_name = trim($matches[1]); // Trim any extra spaces around the product name
            $product_qty = (int)$matches[2];  // Get the product quantity as an integer

            // If this product is already in the array, update its quantity
            if (isset($products_data[$product_name])) {
                $products_data[$product_name]['qty'] += $product_qty;
            } else {
                // Fetch price from the database for the first time the product is seen
                $product_stmt = $conn->prepare("SELECT name, price FROM products WHERE name = ?");
                $product_stmt->execute([$product_name]);
                $product_data = $product_stmt->fetch(PDO::FETCH_ASSOC);

                $price = $product_data ? $product_data['price'] : 0;

                $products_data[$product_name] = [
                    'name' => $product_name,
                    'qty' => $product_qty,
                    'price' => $price
                ];
            }
        }
    }

    // Create PDF receipt
    require('fpdf/fpdf.php');

    // Create PDF with small receipt paper size
    $pdf = new FPDF('P', 'mm', [90, 200]);
    $pdf->AddPage();
    $pdf->SetMargins(5, 5, 5);

    // Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, 'Kape Milagrosa', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(0, 4, 'Your Store Address', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Phone: (XXX) XXX-XXXX', 0, 1, 'C');
    $pdf->Ln(3);

    // Cashier and date
    $pdf->Cell(0, 4, "Cashier: $cashier_name", 0, 1, 'L');
    $pdf->Cell(0, 4, "Date: " . date('d-M-Y H:i'), 0, 1, 'L');
    $pdf->Ln(2);

    // Products
    $pdf->SetFont('Courier', '', 9); // Use monospaced font
    $total_price = 0;

    foreach ($products_data as $product) {
        $subtotal = $product['price'] * $product['qty'];
        $total_price += $subtotal;

        $line = str_pad($product['name'], 18); // 18-char width for product name
        $line .= str_pad('x' . $product['qty'], 5, ' ', STR_PAD_LEFT);
        $line .= str_pad('Php ' . number_format($product['price'], 2), 15, ' ', STR_PAD_LEFT);
        $pdf->Cell(0, 4, $line, 0, 1);
    }

    // Separator line
    $pdf->Ln(2);
    $pdf->Cell(0, 0, str_repeat('-', 50), 0, 1);
    $pdf->Ln(1);

    // Total
    $pdf->SetFont('Courier', 'B', 9);
    $line = str_pad('TOTAL:', 20);
    $line .= str_pad('Php ' . number_format($total_price, 2), 20, ' ', STR_PAD_LEFT);
    $pdf->Cell(0, 5, $line, 0, 1);

    // Footer
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 4, 'Thank you for your purchase!', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Please visit us again.', 0, 1, 'C');
    $pdf->Cell(0, 4, 'Inquiries: (XXX) XXX-XXXX', 0, 1, 'C');

    // Save PDF
    $receipts_dir = 'receipts/';
    if (!is_dir($receipts_dir)) {
        mkdir($receipts_dir, 0755, true);
    }

    $unique_id = uniqid();
    $pdf_filename = $receipts_dir . 'receipt_' . $cashier_id . '_' . date('Ymd_His') . '_' . $unique_id . '.pdf';
    $pdf->Output('F', $pdf_filename);

    $db_filename = basename($pdf_filename);

    // Update order with receipt
    $update = $conn->prepare("UPDATE orders SET receipt = ? WHERE id = ?");
    $update->execute([$db_filename, $order_id]);

    $receipt_file = 'receipts/' . $db_filename;
} else {
    // Use existing receipt
    $receipt_file = 'receipts/' . $order['receipt'];
}

// Check if the file exists before serving
if (!file_exists($receipt_file)) {
    header('location:cashier_order_error.php');
    exit();
}

// Serve PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($receipt_file) . '"');
header('Content-Transfer-Encoding: binary');
header('Accept-Ranges: bytes');
@readfile($receipt_file);
exit();
?>
