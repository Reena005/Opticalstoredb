<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | Optical Store</title>
<style>
    

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: #f4f6f8;
    }

    .navbar {
        background: linear-gradient(135deg, #007bff, #00d4ff);
        color: white;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .navbar h1 {
        margin: 0;
        font-size: 22px;
    }

    .navbar a {
        color: white;
        text-decoration: none;
        font-weight: bold;
        background: rgba(255,255,255,0.2);
        padding: 8px 14px;
        border-radius: 6px;
        transition: 0.3s;
    }

    .navbar a:hover {
        background: rgba(255,255,255,0.4);
    }

    .dashboard {
        padding: 40px;
        text-align: center;
    }

    .dashboard h2 {
        color: #333;
    }

    .card-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 40px;
        gap: 20px;
    }

    .card {
        background: white;
        width: 230px;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card h3 {
        margin: 0 0 10px;
        color: #007bff;
    }

    .card a {
        text-decoration: none;
        color: #333;
        font-weight: bold;
    }

    .logout {
        position: absolute;
        top: 20px;
        right: 30px;
    }
</style>
</head>
<body>

<div class="navbar">
    <h1>👓 Optical Store - Admin Dashboard</h1>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="dashboard">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin']); ?> 👋</h2>
    <p>Manage your store from here:</p>

    <div class="card-container">
        <div class="card">
            <h3>🕶️ Glasses</h3>
            <a href="select_category.php">Manage Glasses</a>
          
        </div>
        <div class="card">
            <h3>👥 Customers</h3>
            <a href="customers.php">View Customers</a>
        </div>
        <div class="card">
            <h3>🚚 Suppliers</h3>
            <a href="suppliers.php">Manage Suppliers</a>
        </div>
        <div class="card">
            <h3>🛒 Orders</h3>
            <a href="orders.php">Add Orders</a>
        </div>
        <div class="card">
            <h3>🛒View  Orders</h3>
            <a href="vieworders.php">View Orders</a>
        </div>
         <div class="card">
            <h3>Eyeproblem</h3>
            <a href="power.php">Add Eyeproblem</a>
        </div>
         <div class="card">
            <h3>Bills</h3>
            <a href="bill.php">Generate bill</a>
        </div>
         
    </div>
</div>
<br>
<br>
<!-- Professional Footer Start -->
<footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
    <div style="max-width: 1000px; margin: auto; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">
        
        <!-- Left Column: Store Info & Copyright -->
        <div style="flex: 1; min-width: 250px; margin-bottom: 15px;">
            <h3 style="margin: 0 0 10px 0; font-size: 20px;">Clarity Store</h3>
            <p style="margin: 2px 0; font-size: 14px;">Guindy, Chennai, Tamil Nadu</p>
            <p style="margin: 2px 0; font-size: 14px;">Phone: 123-456-7890 | Email: info@claritystore.com</p>
            <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
        </div>

        <!-- Right Column: Social Media Icons -->
        
</footer>



</body>
</html>
