<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connect to PostgreSQL using PDO
try {
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

// ======================
// Fetch Orders with Items
// ======================
$sql = "SELECT o.orderid, o.customer_id, o.total_amount, o.status, o.order_date,
               c.customer_name, c.customer_email,
               oi.orderitemid, oi.glassid, oi.quantity, oi.price,
               g.name as glass_name
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.customer_id
        LEFT JOIN order_items oi ON o.orderid = oi.orderid
        LEFT JOIN glass g ON oi.glassid = g.glassid
        ORDER BY o.order_date DESC, o.orderid, oi.orderitemid";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group items by order
$orders = [];
foreach ($rows as $row) {
    $oid = $row['orderid'];
    if (!isset($orders[$oid])) {
        $orders[$oid] = [
            'orderid' => $oid,
            'customer_name' => $row['customer_name'] ?? "Guest",
            'customer_email' => $row['customer_email'] ?? "-",
            'total_amount' => $row['total_amount'],
            'status' => $row['status'],
            'order_date' => $row['order_date'],
            'items' => []
        ];
    }
    if ($row['orderitemid']) {
        $orders[$oid]['items'][] = [
            'glass_name' => $row['glass_name'],
            'quantity' => $row['quantity'],
            'price' => $row['price']
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders Management</title>
<style>
body { font-family: 'Poppins', sans-serif; background: #f4f6fa; margin:0; padding:20px; }
.container { max-width: 1000px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
h2 { text-align:center; color:#333; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
th, td { border:1px solid #ccc; padding:10px; text-align:center; }
th { background:#007bff; color:white; }
tr:nth-child(even) { background-color: #f2f8ff; }
.items-table { width:90%; margin:10px auto; border:1px dashed #ccc; }
.items-table th, .items-table td { border:none; padding:5px; font-size:14px; text-align:center; }
.status-pending { color:orange; font-weight:600; }
.status-shipped { color:blue; font-weight:600; }
.status-delivered { color:green; font-weight:600; }
.status-completed { color:darkgreen; font-weight:600; }
</style>
</head>
<body>

<div class="container">
<h2>Orders List</h2>

<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Email</th>
            <th>Total ($)</th>
            <th>Status</th>
            <th>Date</th>
            <th>Items</th>
        </tr>
    </thead>
    <tbody>
        <?php if(empty($orders)): ?>
            <tr><td colspan="7">No orders found.</td></tr>
        <?php else: ?>
            <?php foreach($orders as $o): ?>
            <tr>
                <td><?= $o['orderid'] ?></td>
                <td><?= htmlspecialchars($o['customer_name']) ?></td>
                <td><?= htmlspecialchars($o['customer_email']) ?></td>
                <td><?= number_format($o['total_amount'], 2) ?></td>
                <td class="status-<?= strtolower($o['status']) ?>"><?= htmlspecialchars($o['status']) ?></td>
                <td><?= date("d-m-Y H:i", strtotime($o['order_date'])) ?></td>
                <td>
                    <table class="items-table">
                        <tr>
                            <th>Glass</th>
                            <th>Qty</th>
                            <th>Price ($)</th>
                        </tr>
                        <?php foreach($o['items'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['glass_name']) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div>

</body>
</html>
