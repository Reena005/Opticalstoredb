<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch customers and glasses for dropdowns
$customers_result = pg_query($conn, "SELECT customer_id, customer_name FROM customers ORDER BY customer_name ASC");
$glasses_result = pg_query($conn, "SELECT glassid, name, price FROM glass ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_order'])) {
    $customer_id = intval($_POST['customer_id']);
    $total_amount = floatval($_POST['total_amount']);
    $status = trim($_POST['status']);
    $payment_method = trim($_POST['payment_method']);
    $payment_status = trim($_POST['payment_status']);

    $glass_ids = $_POST['glass_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $prices = $_POST['price'] ?? [];

    if ($customer_id && $total_amount && count($glass_ids) > 0) {
        // Insert order
        $query = "INSERT INTO orders (customer_id, total_amount, status, payment_method, payment_status) 
                  VALUES ($1, $2, $3, $4, $5) RETURNING orderid";
        $result = pg_query_params($conn, $query, [$customer_id, $total_amount, $status, $payment_method, $payment_status]);

        if ($result) {
            $order = pg_fetch_assoc($result);
            $orderid = $order['orderid'];

            // Insert order items
            for ($i = 0; $i < count($glass_ids); $i++) {
                if ($glass_ids[$i] && $quantities[$i] > 0) {
                    $item_query = "INSERT INTO order_items (orderid, glassid, quantity, price) VALUES ($1,$2,$3,$4)";
                    pg_query_params($conn, $item_query, [intval($orderid), intval($glass_ids[$i]), intval($quantities[$i]), floatval($prices[$i])]);
                }
            }

            // Insert payment record if payment is Paid
            if ($payment_status === 'Paid') {
                $payment_query = "INSERT INTO payment (orderid, amount, payment_method, payment_status) VALUES ($1, $2, $3, $4)";
                pg_query_params($conn, $payment_query, [$orderid, $total_amount, $payment_method, $payment_status]);
            }

            $success = "✅ Order added successfully with items!";
        } else {
            $error = "❌ Failed to add order: " . pg_last_error($conn);
        }
    } else {
        $error = "❌ Customer, Total Amount, and at least one order item are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Order | Optical Store Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #eef2f7; margin:0; padding:0; }
header { background:#4a90e2; color:white; padding:20px; text-align:center; font-size:24px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.container { max-width:700px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:25px; color:#333; }
.form-control { margin-bottom:20px; }
.form-control label { display:block; margin-bottom:8px; font-weight:600; color:#555;}
.form-control input, .form-control select { width:100%; padding:12px 15px; border-radius:8px; border:1px solid #ccc; font-size:15px; transition:0.3s; }
.form-control input:focus, .form-control select:focus { border-color:#4a90e2; outline:none; box-shadow:0 0 5px rgba(74,144,226,0.3);}
button { background:#4a90e2; color:white; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; font-weight:600; width:100%; font-size:16px; transition:0.3s; }
button:hover { background:#357ABD;}
.message { padding:12px; margin-bottom:20px; border-radius:8px; font-weight:500; text-align:center; }
.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }
.order-items { margin-bottom: 20px; }
.order-items .item-row { display:flex; gap:10px; margin-bottom:10px; }
.order-items select, .order-items input { flex:1; padding:8px; border-radius:6px; border:1px solid #ccc;}
.order-items button.add-item { background:#28a745; margin-top:10px; width:auto; padding:8px 15px;}
.order-items button.add-item:hover { background:#218838;}
@media (max-width:700px){.container{margin:20px;padding:20px;}.order-items .item-row {flex-direction: column;}}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
function addItemRow() {
    const container = document.getElementById('order-items-container');
    const template = document.querySelector('.item-row-template');
    const newRow = template.cloneNode(true);
    newRow.classList.remove('item-row-template');
    newRow.style.display = 'flex';
    container.appendChild(newRow);
}

window.addEventListener('DOMContentLoaded', () => {
    const paymentSelect = document.getElementById('payment_method');
    const qrContainer = document.getElementById('gpay-qrcode');

    // Replace with your actual GPay UPI payment link
    const gpayUPI = "upi://pay?pa=merchant@upi&pn=OpticalStore&mc=1234&tr=ORDER123&tn=OrderPayment&am=100&cu=INR";

    paymentSelect.addEventListener('change', function() {
        if (this.value === 'GPay') {
            qrContainer.style.display = 'block';
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: gpayUPI,
                width: 200,
                height: 200
            });
        } else {
            qrContainer.style.display = 'none';
            qrContainer.innerHTML = '';
        }
    });
});
</script>
</head>
<body>

<header>Optical Store Admin Panel</header>
<div class="container">
    <h2>Add New Order</h2>
    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

    <form method="POST">
        <div class="form-control">
            <label>Customer:</label>
            <select name="customer_id" required>
                <option value="">Select Customer</option>
                <?php while($row = pg_fetch_assoc($customers_result)) { ?>
                    <option value="<?= $row['customer_id'] ?>"><?= htmlspecialchars($row['customer_name']) ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="order-items">
            <label>Order Items:</label>
            <div id="order-items-container">
                <div class="item-row item-row-template" style="display:none;">
                    <select name="glass_id[]">
                        <option value="">Select Glass</option>
                        <?php 
                        pg_result_seek($glasses_result, 0);
                        while($glass = pg_fetch_assoc($glasses_result)) { ?>
                            <option value="<?= $glass['glassid'] ?>" data-price="<?= $glass['price'] ?>">
                                <?= htmlspecialchars($glass['name']) ?> - $<?= $glass['price'] ?>
                            </option>
                        <?php } ?>
                    </select>
                    <input type="number" name="quantity[]" min="1" placeholder="Qty">
                    <input type="number" name="price[]" step="0.01" placeholder="Price">
                </div>
            </div>
            <button type="button" class="add-item" onclick="addItemRow()">+ Add Item</button>
        </div>

        <div class="form-control">
            <label>Total Amount:</label>
            <input type="number" name="total_amount" step="0.01" placeholder="Total Amount" required>
        </div>

        <div class="form-control">
            <label>Status:</label>
            <select name="status">
                <option value="Pending">Pending</option>
                <option value="Processing">Processing</option>
                <option value="Completed">Completed</option>
            </select>
        </div>

        <div class="form-control">
            <label>Payment Method:</label>
            <select name="payment_method" id="payment_method">
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="PayPal">PayPal</option>
                <option value="GPay">GPay</option>
            </select>
        </div>

        <!-- GPay QR Code -->
        <div id="gpay-qrcode" style="display:none; text-align:center; margin-bottom:20px;"></div>

        <div class="form-control">
            <label>Payment Status:</label>
            <select name="payment_status">
                <option value="Unpaid">Unpaid</option>
                <option value="Paid">Paid</option>
            </select>
        </div>

        <button type="submit" name="add_order">Add Order</button>
    </form>
</div>

</body>
</html>
