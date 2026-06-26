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
if ($orderId <= 0) { header("Location: orders.php"); exit; }

// Fetch order info
$stmtOrder = $pdo->prepare("SELECT * FROM orders WHERE orderid = :orderid");
$stmtOrder->execute(['orderid' => $orderId]);
$order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
if (!$order) die("Order not found!");

// Fetch customer info
$stmtCustomer = $pdo->prepare("SELECT * FROM customers WHERE customer_id = :cid");
$stmtCustomer->execute(['cid' => $order['customer_id']]);
$customer = $stmtCustomer->fetch(PDO::FETCH_ASSOC);

// Fetch order items
$stmtItems = $pdo->prepare("
    SELECT oi.quantity, oi.price, g.name AS glass_name
    FROM order_items oi
    JOIN glass g ON oi.glassid = g.glassid
    WHERE oi.orderid = :orderid
");
$stmtItems->execute(['orderid' => $orderId]);
$orderItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

// Fetch payments
$stmtPayment = $pdo->prepare("
    SELECT SUM(amount) AS total_paid, MAX(payment_date) AS last_payment
    FROM payments
    WHERE orderid = :orderid
");
$stmtPayment->execute(['orderid' => $orderId]);
$payment = $stmtPayment->fetch(PDO::FETCH_ASSOC);

$total = 0;
foreach ($orderItems as $item) $total += $item['quantity'] * $item['price'];
$tax = round($total * 0.05, 2);
$discount = 0;
$finalTotal = $total + $tax - $discount;
$paidAmount = $payment['total_paid'] ?? 0;
$balance = max($finalTotal - $paidAmount,0);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_amount'])) {
    $payAmount = floatval($_POST['pay_amount']);
    $method = $_POST['payment_method'] ?? 'Cash';

    if($payAmount > 0) {
        $stmtInsert = $pdo->prepare("INSERT INTO payments(orderid, amount, payment_method, payment_date) VALUES(:orderid, :amount, :method, NOW())");
        $stmtInsert->execute(['orderid'=>$orderId, 'amount'=>$payAmount, 'method'=>$method]);
        // Reload page to update paid amount and balance
        header("Location: bill.php?orderid=".$orderId);
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bill #<?= htmlspecialchars($orderId) ?></title>
    <style>
        body { font-family: 'Helvetica', sans-serif; background: #f4f7fb; margin: 0; padding: 0; }
        .bill-container { max-width: 800px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 3px 20px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #007BFF; margin: 0; }
        .info { display: flex; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; }
        .info div { width: 48%; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #007BFF; padding: 12px; text-align: left; }
        th { background-color: #007BFF; color: #fff; }
        tr:nth-child(even) { background-color: #f2f8ff; }
        tfoot td { font-weight: bold; background-color: #cce0ff; }
        .payment-section { margin-top: 30px; text-align: center; }
        .pay-btn, .confirm-btn { background: #007BFF; color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; margin: 5px; font-weight: 600; }
        .pay-btn:hover, .confirm-btn:hover { background: #0056b3; }
        .print-btn { background: #28a745; color: #fff; border: none; padding: 12px 20px; border-radius: 5px; cursor: pointer; display: block; margin: 20px auto; font-size: 16px; }
        .print-btn:hover { background: #218838; }
        input[type="number"] { padding: 8px; border-radius: 5px; border:1px solid #ccc; width:100px; }
        select { padding:8px; border-radius:5px; border:1px solid #ccc; }
        @media print { .payment-section, .print-btn { display: none; } }
    </style>
</head>
<body>
<div class="bill-container">
    <div class="header">
        <h1>Clarity Optical Store</h1>
        <p>Guindy, Chennai, Tamil Nadu | Phone: 123-456-7890</p>
    </div>

    <div class="info">
        <div>
            <h3>Customer Details</h3>
            <p><strong>Name:</strong> <?= htmlspecialchars($customer['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($customer['customer_email'] ?? '-') ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($customer['customer_contact'] ?? '-') ?></p>
        </div>
        <div>
            <h3>Order Details</h3>
            <p><strong>Order ID:</strong> <?= htmlspecialchars($orderId) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
            <p><strong>Last Payment:</strong> <?= htmlspecialchars($payment['last_payment'] ?? '-') ?></p>
        </div>
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
            $itemTotal = $item['quantity'] * $item['price']; ?>
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
            <td colspan="3">Tax (5%)</td>
            <td><?= '$'.number_format($tax,2) ?></td>
        </tr>
        <tr>
            <td colspan="3">Discount</td>
            <td><?= '$'.number_format($discount,2) ?></td>
        </tr>
        <tr>
            <td colspan="3">Total</td>
            <td><?= '$'.number_format($finalTotal,2) ?></td>
        </tr>
        <tr>
            <td colspan="3">Paid Amount</td>
            <td><?= '$'.number_format($paidAmount,2) ?></td>
        </tr>
        <tr>
            <td colspan="3">Balance</td>
            <td><?= '$'.number_format($balance,2) ?></td>
        </tr>
        </tfoot>
    </table>

    <?php if($balance > 0): ?>
    <div class="payment-section">
        <h3>Make Payment</h3>
        <form method="post">
            <label>Amount to Pay: </label>
            <input type="number" step="0.01" max="<?= $balance ?>" name="pay_amount" value="<?= $balance ?>" required>
            <select name="payment_method">
                <option value="Cash">Cash</option>
                <option value="GPay">GPay</option>
                <option value="Card">Card</option>
            </select>
            <button type="submit" class="confirm-btn">Mark as Paid</button>
        </form>
    </div>
    <?php endif; ?>

    <button class="print-btn" onclick="window.print()">Print Bill</button>
</div>
</body>
</html>
