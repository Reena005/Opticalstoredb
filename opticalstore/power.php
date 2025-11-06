<?php
include('includes.php'); // database connection
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch customers for dropdown
$customers_result = pg_query($conn, "SELECT customer_id, customer_name FROM customers ORDER BY customer_name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_deduction'])) {
    $customer_id = intval($_POST['customer_id']);
    $eye_left = !empty($_POST['eye_left']) ? floatval($_POST['eye_left']) : null;
    $eye_right = !empty($_POST['eye_right']) ? floatval($_POST['eye_right']) : null;
    $deduction_amount = floatval($_POST['deduction_amount']);
    $deduction_date = $_POST['deduction_date'];
    $notes = trim($_POST['notes']);

    // PHP-side validation
    if (!$customer_id) {
        $error = "❌ Please select a customer.";
    } elseif ($eye_left === null && $eye_right === null) {
        $error = "❌ Please enter at least one eye power.";
    } elseif ($deduction_amount < 0) {
        $error = "❌ Deduction amount cannot be negative.";
    } elseif (($eye_left !== null && $eye_left > 20) || ($eye_right !== null && $eye_right > 20)) {
        $error = "❌ Eye power cannot exceed 20.";
    } else {
        // Call PostgreSQL function
        $query = "SELECT add_eye_deduction($1, $2, $3, $4, $5, $6)";
        $result = pg_query_params($conn, $query, [$customer_id, $eye_left, $eye_right, $deduction_amount, $deduction_date, $notes]);

        if ($result) {
            $success = "✅ Eye power deduction added successfully!";
        } else {
            $error = "❌ Failed to add deduction: " . pg_last_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Eye Power Deduction | Optical Store Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #eef2f7; margin:0; padding:0; }
header { background:#4a90e2; color:white; padding:20px; text-align:center; font-size:24px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.container { max-width:600px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:25px; color:#333; }
.form-control { margin-bottom:20px; }
.form-control label { display:block; margin-bottom:8px; font-weight:600; color:#555;}
.form-control input, .form-control select, .form-control textarea { width:100%; padding:12px 15px; border-radius:8px; border:1px solid #ccc; font-size:15px; transition:0.3s; }
.form-control input:focus, .form-control select:focus, .form-control textarea:focus { border-color:#4a90e2; outline:none; box-shadow:0 0 5px rgba(74,144,226,0.3);}
button { background:#4a90e2; color:white; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; font-weight:600; width:100%; font-size:16px; transition:0.3s; }
button:hover { background:#357ABD;}
.message { padding:12px; margin-bottom:20px; border-radius:8px; font-weight:500; text-align:center; }
.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }
</style>
</head>
<body>
    <div class="navbar">
    

<header>Optical Store EyeProblem Information Panel</header>
<div class="container">
    <h2>Add Customer Eye Power Deduction</h2>
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

        <div class="form-control">
            <label>Eye Power Left:</label>
            <input type="number" step="0.01" name="eye_left" placeholder="e.g., 1.25">
        </div>

        <div class="form-control">
            <label>Eye Power Right:</label>
            <input type="number" step="0.01" name="eye_right" placeholder="e.g., 1.00">
        </div>

        <div class="form-control">
            <label>Deduction Amount:</label>
            <input type="number" step="0.01" name="deduction_amount" placeholder="Amount" required>
        </div>

        <div class="form-control">
            <label>Deduction Date:</label>
            <input type="date" name="deduction_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="form-control">
            <label>Notes:</label>
            <textarea name="notes" rows="3" placeholder="Optional notes..."></textarea>
        </div>

        <button type="submit" name="add_deduction">Add Deduction</button>
    </form>
</div>

<footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
    <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
</footer>

</body>
</html>
