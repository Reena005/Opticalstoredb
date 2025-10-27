<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch orders with customer names
$query = "SELECT o.orderid, o.order_date, o.total_amount, o.status, c.customer_name
          FROM orders o
          JOIN customers c ON o.customer_id = c.customer_id
          ORDER BY o.order_date DESC";

$result = pg_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Orders | Optical Store Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #eef2f7; margin:0; padding:0; }
header { background:#4a90e2; color:white; padding:20px; text-align:center; font-size:24px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.container { max-width:900px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:25px; color:#333; }
table { width:100%; border-collapse: collapse; margin-top:20px; }
table, th, td { border:1px solid #ccc; }
th, td { padding:12px; text-align:left; }
th { background:#4a90e2; color:white; }
tr:nth-child(even) { background:#f2f2f2; }
.status-Pending { color:#856404; font-weight:600; }
.status-Processing { color:#0c5460; font-weight:600; }
.status-Completed { color:#155724; font-weight:600; }
</style>
</head>
<body>

<header>Optical Store Order Panel</header>

<div class="container">
    <h2>All Orders</h2>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Total Amount</th>
            <th>Status</th>
        </tr>
        <?php while($row = pg_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $row['orderid'] ?></td>
            <td><?= htmlspecialchars($row['customer_name']) ?></td>
            <td><?= date('d-m-Y H:i', strtotime($row['order_date'])) ?></td>
            <td>$<?= number_format($row['total_amount'], 2) ?></td>
            <td class="status-<?= $row['status'] ?>"><?= htmlspecialchars($row['status']) ?></td>
        </tr>
        <?php } ?>
    </table>
</div>
<footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
    <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
</footer>


</body>
</html>
