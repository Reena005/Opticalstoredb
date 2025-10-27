<?php
include('includes.php');
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// 🆕 Delete customer
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

// Add new customer
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_customer'])) {
    $name = trim($_POST['customer_name']);
    $email = trim($_POST['customer_email']);
    $country = trim($_POST['customer_country']);
    $city = trim($_POST['customer_city']);
    $contact = trim($_POST['customer_contact']);
    $address = trim($_POST['customer_address']);
    $confirm_code = trim($_POST['c_code']);

    if ($name && $email && $country && $city && $contact && $address && $confirm_code) {
        $query = "INSERT INTO customers 
                  (customer_name, customer_email, customer_country, customer_city, customer_contact, customer_address, c_code) 
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Customers | Optical Store Admin</title>
<style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f6f8; }
    .navbar {
        background: linear-gradient(135deg, #007bff, #00d4ff);
        color: white; padding: 15px 30px; display: flex;
        justify-content: space-between; align-items: center;
    }
    .navbar a { color: white; text-decoration: none; font-weight: bold; margin-left: 15px; }
    .container { padding: 30px; max-width: 1200px; margin: auto; }
    h2 { color: #007bff; }
    .form-control { margin-bottom: 15px; }
    .form-control input, .form-control textarea {
        width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px;
    }
    textarea { resize: vertical; }
    button {
        background: linear-gradient(135deg, #007bff, #00d4ff); color: white;
        border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold;
    }
    button:hover { background: linear-gradient(135deg, #0056b3, #009edc); }
    table {
        width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px;
        overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    table th { background: #007bff; color: white; }
    .message { margin: 10px 0; padding: 10px; border-radius: 6px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .logout-btn {
        background: #ff4d4d; padding: 8px 15px; border-radius: 6px; font-weight: bold;
        text-decoration: none; color: white;
    }
    .logout-btn:hover { background: #cc0000; }
    /* 🆕 Delete button style */
    .delete-btn {
        background: #ff4d4d;
        color: white;
        border: none;
        border-radius: 5px;
        padding: 6px 10px;
        cursor: pointer;
        transition: 0.3s;
    }
    .delete-btn:hover {
        background: #cc0000;
    }
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
        <div class="form-control">
            <input type="text" name="customer_name" placeholder="Customer Name" required>
        </div>
        <div class="form-control">
            <input type="email" name="customer_email" placeholder="Email Address" required>
        </div>
        <div class="form-control">
            <input type="text" name="customer_country" placeholder="Country" required>
        </div>
        <div class="form-control">
            <input type="text" name="customer_city" placeholder="City" required>
        </div>
        <div class="form-control">
            <input type="text" name="customer_contact" placeholder="Contact Number" required>
        </div>
        <div class="form-control">
            <textarea name="customer_address" placeholder="Address" rows="3" required></textarea>
        </div>
        <div class="form-control">
            <input type="text" name="c_code" placeholder="Confirmation Code" required>
        </div>
        <button type="submit" name="add_customer">Add Customer</button>
    </form>

    <h2>All Customers</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Country</th>
            <th>City</th>
            <th>Contact</th>
            <th>Address</th>
            <th>Confirmation Code</th>
            <th>Action</th> <!-- 🆕 New Column -->
        </tr>
        <?php
        if ($customers_result) {
            while ($row = pg_fetch_assoc($customers_result)) {
                echo "<tr>
                        <td>{$row['customer_id']}</td>
                        <td>{$row['customer_name']}</td>
                        <td>{$row['customer_email']}</td>
                        <td>{$row['customer_country']}</td>
                        <td>{$row['customer_city']}</td>
                        <td>{$row['customer_contact']}</td>
                        <td>{$row['customer_address']}</td>
                        <td>{$row['c_code']}</td>
                        <td>
                            <a href='?delete_id={$row['customer_id']}' onclick=\"return confirm('Are you sure you want to delete this customer?');\">
                                <button type='button' class='delete-btn'>Delete</button>
                            </a>
                        </td>
                      </tr>";
            }
        }
        ?>
    </table>
</div>
<footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
<!-- Footer End -->
 <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
</footer>

</body>
</html>
