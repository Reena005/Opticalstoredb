<?php
session_start();

// ✅ Remove single item
if (isset($_POST['remove'])) {
    $remove_index = $_POST['remove_index'];
    if (isset($_SESSION['cart'][$remove_index])) {
        unset($_SESSION['cart'][$remove_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: cart.php");
    exit;
}

// ✅ Clear all items
if (isset($_POST['clear_all'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit;
}

// ✅ Place order and redirect to orders.php
if (isset($_POST['place_order']) && !empty($_SESSION['cart'])) {
    if (!isset($_SESSION['orders'])) {
        $_SESSION['orders'] = [];
    }

    $_SESSION['orders'][] = $_SESSION['cart'];
    unset($_SESSION['cart']);

    header("Location: orders.php");
    exit;
}

// ✅ Update quantities
if (isset($_POST['update_qty'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        $qty = max(1, intval($qty)); // ensure at least 1
        if (isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['quantity'] = $qty;
        }
    }
    header("Location: cart.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart | Clarity Store</title>
<style>
body { font-family: Arial, sans-serif; background: #f8fafc; padding: 0; margin:0; }
nav { background: linear-gradient(135deg, #007bff, #00d4ff); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
nav a { color: white; text-decoration: none; font-weight: 500; margin-left: 20px; transition: 0.3s; }
nav a:hover { text-decoration: underline; }
.container { padding: 30px; text-align: center; }
h2 { color: #333; margin-bottom: 20px; }
table { border-collapse: collapse; width: 90%; margin: 20px auto; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);}
th, td { padding: 12px 15px; text-align: center; border-bottom: 1px solid #eee; }
th { background: #007bff; color: white; }
tr:hover { background: #f1f1f1; }
input.qty-input { width: 50px; text-align: center; }
.remove-btn, .clear-btn, .continue-btn, .update-btn { cursor: pointer; transition: 0.3s; }
.remove-btn { background: #ff4d4d; color: white; border: none; padding: 6px 10px; border-radius: 5px; }
.remove-btn:hover { background: #d63333; transform: scale(1.05); }
.clear-btn { background: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 25px; }
.clear-btn:hover { background: #5a6268; transform: scale(1.05); }
.update-btn { background: #28a745; color: white; border: none; padding: 6px 10px; border-radius: 5px; }
.update-btn:hover { background: #218838; transform: scale(1.05); }
.continue-btn { background: linear-gradient(135deg, #007bff, #00d4ff); color: white; padding: 12px 25px; border-radius: 50px; text-decoration: none; font-size: 16px; font-weight: 500; display: inline-block; margin:5px; }
.continue-btn:hover { background: linear-gradient(135deg, #0056b3, #00a3ff); transform: scale(1.05); }
.actions { margin-top: 20px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap; }
p.empty-cart { font-size: 20px; color: #555; margin-top: 30px; }
</style>
</head>
<body>

<nav>
    <div>👓 Clarity Store</div>
    <div>
        <a href="glass.php">Continue Shopping</a>
        <a href="cart.php">🛒 Cart (<?= !empty($_SESSION['cart']) ? count($_SESSION['cart']) : 0 ?>)</a>
    </div>
</nav>

<div class="container">
<h2>🛒 Your Shopping Cart</h2>

<?php if (!empty($_SESSION['cart'])): ?>
<form action="cart.php" method="POST">
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price (₹)</th>
        <th>Quantity</th>
        <th>Subtotal (₹)</th>
        <th>Action</th>
    </tr>
    <?php
    $grand_total = 0;
    foreach ($_SESSION['cart'] as $index => $item):
        $quantity = isset($item['quantity']) ? $item['quantity'] : 1;
        $subtotal = $item['price'] * $quantity;
        $grand_total += $subtotal;
    ?>
    <tr>
        <td><?= $index + 1 ?></td>
        <td><?= htmlspecialchars($item['name']) ?></td>
        <td><?= htmlspecialchars($item['price']) ?></td>
        <td>
            <input type="number" min="1" name="quantity[<?= $index ?>]" value="<?= $quantity ?>" class="qty-input">
        </td>
        <td><?= $subtotal ?></td>
        <td>
            <form action="cart.php" method="POST" style="display:inline;">
                <input type="hidden" name="remove_index" value="<?= $index ?>">
                <button type="submit" name="remove" class="remove-btn">Remove</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
        <td colspan="2"><strong>₹<?= $grand_total ?></strong></td>
    </tr>
</table>


</form>

<form action="cart.php" method="POST">
    <button type="submit" name="clear_all" class="clear-btn">🗑 Clear Cart</button>
</form>

<form action="orders.php" method="POST">
    <button type="submit" name="place_order" class="continue-btn">💳 Checkout</button>
</form>
</div>

<?php else: ?>
<p class="empty-cart">Your cart is empty.</p>
<a href="glass.php" class="continue-btn">⬅ Continue Shopping</a>
<?php endif; ?>

</div>
</body>
</html>
