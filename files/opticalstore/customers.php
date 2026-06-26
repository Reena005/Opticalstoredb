<?php
include('includes.php');
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Function to generate confirmation code
function generateConfirmCode() {
    return 'CONF' . rand(10000, 99999);
}

// Delete customer
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_query = "DELETE FROM customers WHERE customer_id = $1";
    $delete_result = pg_query_params($conn, $delete_query, array($delete_id));

    if ($delete_result) {
        $success = "🗑️ Customer deleted successfully!";
    } else {
        $error = "❌ Failed to delete customer: " . pg_last_error($conn);
    }
}

// Update customer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_customer'])) {
    $id = intval($_POST['customer_id']);
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['customer_email']);
    $country = trim($_POST['customer_country']);
    $city = trim($_POST['customer_city']);
    $contact = trim($_POST['customer_contact']);
    $address = trim($_POST['customer_address']);

    $query = "UPDATE customers SET 
                customer_name = $1,
                customer_email = $2,
                customer_country = $3,
                customer_city = $4,
                customer_contact = $5,
                customer_address = $6
              WHERE customer_id = $7";
    $result = pg_query_params($conn, $query, array($name, $email, $country, $city, $contact, $address, $id));

    if ($result) {
        $success = "✏️ Customer updated successfully!";
    } else {
        $error = "❌ Failed to update customer: " . pg_last_error($conn);
    }
}

// Add new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['customer_email']);
    $country = trim($_POST['customer_country']);
    $city = trim($_POST['customer_city']);
    $contact = trim($_POST['customer_contact']);
    $address = trim($_POST['customer_address']);
    $confirm_code = generateConfirmCode();

    if ($name && $email && $country && $city && $contact && $address) {
        $query = "INSERT INTO customers 
                  (customer_name, customer_email, customer_country, customer_city, customer_contact, customer_address, customer_confirm_code)
                  VALUES ($1, $2, $3, $4, $5, $6, $7)";
        $result = pg_query_params($conn, $query, array($name, $email, $country, $city, $contact, $address, $confirm_code));

        if ($result) {
            $success = "✅ Customer added successfully!";
        } else {
            $error = "❌ Failed to add customer: " . pg_last_error($conn);
        }
    } else {
        $error = "❌ All fields are required.";
    }
}

// Fetch all customers
$customers_result = pg_query($conn, "SELECT * FROM customers ORDER BY customer_id ASC");

// Fetch recent audit logs
$audit_result = pg_query($conn, "SELECT * FROM customer_audit ORDER BY audit_id DESC LIMIT 20");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customers | Optical Store Admin</title>
<style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f6f8; }
    .navbar { background: linear-gradient(135deg, #007bff, #00d4ff); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    .navbar a { color: white; text-decoration: none; font-weight: bold; margin-left: 15px; }
    .container { padding: 30px; max-width: 1200px; margin: auto; }
    h2 { color: #007bff; }
    .form-control { margin-bottom: 15px; }
    .form-control input, .form-control textarea { width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
    textarea { resize: vertical; }
    button { background: linear-gradient(135deg, #007bff, #00d4ff); color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
    button:hover { background: linear-gradient(135deg, #0056b3, #009edc); }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    table th { background: #007bff; color: white; }
    .message { margin: 10px 0; padding: 10px; border-radius: 6px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .logout-btn { background: #ff4d4d; padding: 8px 15px; border-radius: 6px; font-weight: bold; text-decoration: none; color: white; }
    .logout-btn:hover { background: #cc0000; }
    .delete-btn { background: #ff4d4d; color: white; border: none; border-radius: 5px; padding: 6px 10px; cursor: pointer; transition: 0.3s; }
    .delete-btn:hover { background: #cc0000; }
    .audit-insert { color: green; }
    .audit-update { color: blue; }
    .audit-delete { color: red; }
</style>
</head>
<body>

<div class="navbar">
    <h1>👓 Optical Store Admin</h1>
    <div>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Add New Customer</h2>

    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

    <form method="post">
        <div class="form-control"><input type="text" name="customer_name" placeholder="Customer Name" required></div>
        <div class="form-control"><input type="email" name="customer_email" placeholder="Email Address" required></div>
        <div class="form-control"><input type="text" name="customer_country" placeholder="Country" required></div>
        <div class="form-control"><input type="text" name="customer_city" placeholder="City" required></div>
        <div class="form-control"><input type="text" name="customer_contact" placeholder="Contact Number" required></div>
        <div class="form-control"><textarea name="customer_address" placeholder="Address" rows="3" required></textarea></div>
        <button type="submit" name="add_customer">Add Customer</button>
    </form>

   <h2>All Customers</h2>
<table>
    <tr>
        <th>S.No</th><th>ID</th><th>Name</th><th>Email</th><th>Country</th><th>City</th><th>Contact</th><th>Address</th><th>Confirmation Code</th><th>Action</th>
    </tr>
    <?php
    if ($customers_result) {
        $serial = 1; // Initialize serial number
        while ($row = pg_fetch_assoc($customers_result)) {
            echo "<tr>
                    <td>{$serial}</td>
                    <td>{$row['customer_id']}</td>
                    <td>{$row['customer_name']}</td>
                    <td>{$row['customer_email']}</td>
                    <td>{$row['customer_country']}</td>
                    <td>{$row['customer_city']}</td>
                    <td>{$row['customer_contact']}</td>
                    <td>{$row['customer_address']}</td>
                    <td>{$row['customer_confirm_code']}</td>
                    <td>
                        <a href='?delete_id={$row['customer_id']}' onclick=\"return confirm('Are you sure you want to delete this customer?');\">
                            <button type='button' class='delete-btn'>Delete</button>
                        </a>
                    </td>
                  </tr>";
            $serial++; // increment serial number
        }
    }
    ?>
</table>


   <h2>Recent Audit Logs</h2>
<table>
    <tr>
        <th>S.No</th><th>Customer ID</th><th>Action</th><th>Description</th><th>Time</th>
    </tr>
    <?php
    if ($audit_result) {
        $serial = 1; // Initialize serial number
        while ($log = pg_fetch_assoc($audit_result)) {
            $class = '';
            $description = '';

            if ($log['action_type'] == 'INSERT') {
                $class = 'audit-insert';
                $description = "✅ New customer added: " . $log['action_description'];
            } elseif ($log['action_type'] == 'UPDATE') {
                $class = 'audit-update';
                $description = "✏️ Customer updated";
            } elseif ($log['action_type'] == 'DELETE') {
                $class = 'audit-delete';
                $description = "🗑️ Customer deleted: " . $log['action_description'];
            }

            echo "<tr>
                    <td>{$serial}</td>
                    <td>{$log['customer_id']}</td>
                    <td class='$class'>{$log['action_type']}</td>
                    <td>{$description}</td>
                    <td>{$log['action_time']}</td>
                  </tr>";

            $serial++; // increment serial number
        }
    }
    ?>
</table>

</div>

</body>
</html>
