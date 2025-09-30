<?php

namespace Helpers;
require_once 'enums/OrderStatusEnum.php';

use Enums\OrderStatusEnum;
class FormatHelper
{
    public static function formatPrice(float $price): string
    {
        return '₱ ' . number_format($price, 2);
    }

    public static function formatOrders($orders, \PDO $conn): array
    {

        $formatted_orders = [];
        foreach ($orders as $order) {
            $formatted_orders[] = [
                'order_id' => $order['order_id'],
                'name' => htmlspecialchars($order['name']),
                'number' => htmlspecialchars($order['number'] ?? ''),
                'email' => htmlspecialchars($order['email']),
                'placed_on' => date('M d, Y', strtotime($order['placed_on'])),
                'type' => htmlspecialchars($order['type']),
                'receipt' => htmlspecialchars($order['receipt'] ?? ''),
                'status' => [
                    'value' => htmlspecialchars($order['status']),
                    'label' => OrderStatusEnum::from($order['status'])->label(),
                    'color' => OrderStatusEnum::from($order['status'])->color()
                ],
                'total_price' => number_format((int) $order['total_price'], 2),
                'products' => array_filter(array_map(function ($product_id) use ($conn, $order) {
                    // Fetch product details
                    $select_product = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
                    $select_product->execute([$product_id]);
                    $product = $select_product->fetch(\PDO::FETCH_ASSOC);
                    // Check if product exists
                    if (!$product) {
                        return null; // Skip if product not found
                    }

                    // Get the quantity and subtotal from order_products
                    $select_order_product = $conn->prepare("SELECT * FROM `order_products` WHERE order_id = ? AND product_id = ?");
                    $select_order_product->execute([$order['order_id'], $product_id]);
                    $order_product = $select_order_product->fetch(\PDO::FETCH_ASSOC);
                    if (!$order_product) {
                        $order_product = $select_order_product->fetch(\PDO::FETCH_ASSOC);
                    }
                    $product['quantity'] = $order_product['quantity'];
                    $product['subtotal'] = $order_product['subtotal'];

                    if ($product['type'] === 'religious' || $product['type'] === 'coffee') {
                        return [
                            'id' => $product['id'],
                            'name' => htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                            'quantity' => htmlspecialchars($product['quantity'] ?? '0', ENT_QUOTES, 'UTF-8'),
                            'price' => number_format($product['price'] ?? 0, 2),
                            'subtotal' => number_format($product['subtotal'] ?? 0, 2),
                            'cup_sizes' => json_decode($order_product['cup_sizes'] ?? '[]', true) ?: [],
                            'ingredients' => $product['type'] === 'coffee' ? array_map(function ($ingredient) {
                                return [
                                    'name' => htmlspecialchars($ingredient['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                                    'level' => htmlspecialchars($ingredient['level'] ?? '', ENT_QUOTES, 'UTF-8')
                                ];
                            }, json_decode($order_product['ingredients'] ?? '[]', true) ?: []) : [],
                            'add_ons' => $product['type'] === 'coffee' ? array_map(function ($addOn) {
                                return [
                                    'name' => htmlspecialchars($addOn['name'] ?? '', ENT_QUOTES, 'UTF-8'),
                                    'price' => number_format($addOn['price'] ?? 0, 2)
                                ];
                            }, json_decode($order_product['add_ons'] ?? '[]', true) ?: []) : [],
                        ];
                    }
                    return null;
                }, explode(',', $order['product_ids'])))
            ];

            // Sort products by order_id
            usort($formatted_orders, function ($a, $b) {
                return $a['order_id'] <=> $b['order_id'];
            });
        }

        return $formatted_orders;

    }

    public static function formatColor($enum, string $status): string
    {
        $enum = 'Enums\\' . $enum;
        return $enum::from($status)->color();
    }

    public static function formatPath(string $from, string $to): string
    {
        // Normalize paths
        $from = realpath($from);
        $to = realpath($to);

        if (!$from || !$to) {
            return $to; // fallback if realpath fails
        }

        // Split into parts
        $fromParts = explode(DIRECTORY_SEPARATOR, $from);
        $toParts = explode(DIRECTORY_SEPARATOR, $to);

        // Remove common base path
        while (!empty($fromParts) && !empty($toParts) && $fromParts[0] === $toParts[0]) {
            array_shift($fromParts);
            array_shift($toParts);
        }

        // Build relative path
        $upLevels = count($fromParts);

        // If up only once → use "./" instead of "../"
        if ($upLevels === 1) {
            $prefix = './';
        } else {
            $prefix = str_repeat('../', $upLevels);
        }

        $relative = $prefix . implode('/', $toParts);

        // Always return with forward slashes
        return str_replace('\\', '/', $relative);
    }

}
