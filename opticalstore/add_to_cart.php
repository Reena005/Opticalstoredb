<?php
session_start();

// Make sure request is via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create an item array
    $item = [
        'id'    => $_POST['glassid'] ?? '',
        'name'  => $_POST['name'] ?? '',
        'price' => $_POST['price'] ?? 0
    ];

    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Add item to cart
    $_SESSION['cart'][] = $item;

    // Redirect to cart page
    header("Location: cart.php");
    exit;
} else {
    echo "❌ Invalid request method. Please use POST.";
}
