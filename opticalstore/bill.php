<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$orderId = isset($_GET['orderid']) ? intval($_GET['orderid']) : 0;

if ($orderId <= 0) {
    $stmtOrders = $pdo->query("SELECT orderid FROM orders ORDER BY orderid DESC");
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Select Order</title>
        <style>
            body { font-family: 'Arial', sans-serif; margin: 50px; background-color: #f0f4f8; }
            .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; box-shadow: 0 0 15px rgba(0,0,0,0.1);}
            select, button { padding: 10px; font-size: 16px; margin-top: 20px; width: 100%; border-radius: 5px; border: 1px solid #ccc; }
            button { background-color: #007BFF; color: white; border: none; cursor: pointer; }
            button:hover { background-color: #0069d9; }
            header { background:#4a90e2; color:white; padding:20px; text-align:center; font-size:24px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
        </style>
    </head>
    <body>
        <header>Optical Store Bill Panel</header>
        <div class="container">
            <h2>Select Order to Print Bill</h2>
            <form method="get" action="bill.php">
                <label for="orderid">Order ID:</label>
                <select name="orderid" id="orderid" required>
                    <option value="">--Select Order--</option>
                    <?php foreach ($orders as $order): ?>
                        <option value="<?= $order['orderid'] ?>">Order #<?= $order['orderid'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">View Bill</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$stmtItems = $pdo->prepare("
    SELECT oi.quantity, oi.price, g.name AS glass_name
    FROM order_items oi
    JOIN glass g ON oi.glassid = g.glassid
    WHERE oi.orderid = :orderid
");
$stmtItems->execute(['orderid' => $orderId]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

if (!$orderItems) die("No items found for this order.");

$stmtPayment = $pdo->prepare("
    SELECT SUM(amount) AS total_paid, MAX(payment_date) AS last_payment
    FROM payments
    WHERE orderid = :orderid
");
$stmtPayment->execute(['orderid' => $orderId]);
$payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);

if (!$payment || $payment['total_paid'] === null) die("No payment found for this order.");

$total = 0;
foreach ($orderItems as $item) $total += $item['quantity'] * $item['price'];
$balance = $payment['total_paid'] - $total;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bill for Order #<?= htmlspecialchars($orderId) ?></title>
    <style>
        body { font-family: 'Helvetica', sans-serif; background: #e9f0fb; margin: 0; padding: 0; }
        .bill-container { max-width: 800px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 3px 15px rgba(0,0,0,0.1);}
        .company { text-align: center; margin-bottom: 40px; }
        .company h1 { font-size: 32px; color: #007BFF; margin: 0; }
        .company p { font-size: 16px; color: #555; margin: 5px 0 0 0; }
        .info { margin-bottom: 30px; }
        .info p { margin: 5px 0; font-size: 16px; color: #333; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #007BFF; padding: 12px; text-align: left; }
        th { background-color: #007BFF; color: white; }
        tr:nth-child(even) { background-color: #f2f8ff; }
        tr:hover { background-color: #d0e7ff; }
        tfoot td { font-weight: bold; background-color: #cce0ff; }

        .print-btn { background-color: #007BFF; color: white; border: none; padding: 12px 20px; font-size: 16px; cursor: pointer; border-radius: 5px; display: block; margin: 20px auto; }
        .print-btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="bill-container">
        <div class="company">
            <h1>Clarity Store</h1>
            <p>Guindy, Chennai, Tamil Nadu</p>
            <p>Phone: 123-456-7890</p>
        </div>

        <div class="info">
            <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>
            <p><strong>Last Payment Date:</strong> <?= htmlspecialchars($payment['last_payment']) ?></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderItems as $item):
                    $itemTotal = $item['quantity'] * $item['price'];
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['glass_name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= '$'.number_format($item['price'],2) ?></td>
                    <td><?= '$'.number_format($itemTotal,2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Subtotal</td>
                    <td><?= '$'.number_format($total,2) ?></td>
                </tr>
                <tr>
                    <td colspan="3">Paid Amount</td>
                    <td><?= '$'.number_format($payment['total_paid'],2) ?></td>
                </tr>
                <tr>
                    <td colspan="3">Balance</td>
                    <td><?= '$'.number_format($balance,2) ?></td>
                </tr>
            </tfoot>
        </table>

        <button class="print-btn" onclick="window.print()">Print Bill</button>
    </div>
    <footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
    <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
</footer>

</body>
</html>
