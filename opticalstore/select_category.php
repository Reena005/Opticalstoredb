<?php
include('includes.php');
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Fetch all categories
$result = pg_query($conn, "SELECT * FROM categories ORDER BY cat_id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Category | Optical Store Admin</title>
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
.navbar a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    margin-left: 15px;
}
.container {
    max-width: 800px;
    margin: auto;
    padding: 30px;
}
.category-list {
    list-style: none;
    padding: 0;
}
.category-list li {
    background: white;
    padding: 15px 20px;
    margin-bottom: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: background 0.2s;
}
.category-list li a {
    text-decoration: none;
    color: #007bff;
    font-weight: bold;
}
.category-list li:hover {
    background: #e6f0ff;
}
</style>
</head>
<body>

<div class="navbar">
    <h1>👓 Optical Store Admin</h1>
    <div>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Select a Category</h2>
    <ul class="category-list">
        <?php
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                ?>
                <li>
                    <a href="manage_glass.php?cat_id=<?= $row['cat_id'] ?>">
                        <?= htmlspecialchars($row['cat_title']) ?>
                    </a>
                </li>
                <?php
            }
        } else {
            echo "<li>No categories found.</li>";
        }
        ?>
    </ul>
</div>

</body>
</html>
