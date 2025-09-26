<?php

@include 'config.php';

session_start();
session_unset();
session_destroy();

if (!isset($_SESSION['categories'])) {
    $_SESSION['categories'] = [
      [
        'name' => 'Frappe',
        'image' => 'Frappe.jpg',
        'description' => 'Creamy blended coffee drinks topped with whipped cream.',
      ],
      [
        'name' => 'Fruit Soda',
        'image' => 'Fruit Soda.jpg',
        'description' => 'Refreshing carbonated drinks with fruity flavors.',
      ],
  
      [
        'name' => 'Frappe Extreme',
        'image' => 'Frappe Extreme.jpg',
        'description' => 'Extra indulgent frappes with bold flavors and toppings.',
      ],
      [
        'name' => 'Milk Tea',
        'image' => 'Milk Tea.jpg',
        'description' => 'Classic tea mixed with creamy milk and chewy pearls.',
      ],
      [
        'name' => 'Fruit Tea',
        'image' => 'Fruit Tea.jpg',
        'description' => 'Light and refreshing teas infused with real fruits.',
      ],
      [
        'name' => 'Fruit Milk',
        'image' => '',
        'description' => 'Smooth, chilled milk blended with fresh fruit flavors.',
      ],
      [
        'name' => 'Espresso',
        'image' => 'Fruit Milk.jpg',
        'description' => 'Strong, rich shots of pure coffee perfection.',
      ],
      [
        'name' => 'Hot Non-Coffee',
        'image' => 'Hot Non-Coffee.png',
        'description' => 'Warm drinks without coffee for cozy moments.',
      ],
      [
        'name' => 'Iced Non-Coffee',
        'image' => 'Iced Non-Coffee.jpg',
        'description' => 'Chilled beverages for non-coffee lovers.',
      ],
      [
        'name' => 'Snacks',
        'image' => 'Snacks.jpg',
        'description' => 'Delicious bites to pair with your favorite drinks.',
      ],
    ];
  
  }

header('location:index.php');

?>