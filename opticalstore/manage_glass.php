<?php
include('includes.php');
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Flash messages
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Handle Add New Glass
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_glass'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $material = trim($_POST['material']);
    $rim = trim($_POST['rim']);
    $shape = trim($_POST['shape']);
    $feature = trim($_POST['feature']);
    $framewidth = trim($_POST['framewidth']);

    // Handle image upload
    $image_name = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = uniqid('glass_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/categories/$image_name");
    }

    if ($name && $price && $material) {
        $insert_glass = pg_query_params($conn,
            "INSERT INTO glass (name, price, material, image) VALUES ($1, $2, $3, $4) RETURNING glassid",
            [$name, $price, $material, $image_name]
        );
        $glass_row = pg_fetch_assoc($insert_glass);
        $new_glass_id = $glass_row['glassid'];

        pg_query_params($conn,
            "INSERT INTO glassdescription (glassid, rim, shape, feature, framewidth) VALUES ($1, $2, $3, $4, $5)",
            [$new_glass_id, $rim, $shape, $feature, $framewidth]
        );

        $_SESSION['success'] = "✅ Glass added successfully!";
        header("Location: manage_glass.php");
        exit;
    } else {
        $_SESSION['error'] = "❌ Please fill all required fields (Name, Price, Material).";
        header("Location: manage_glass.php");
        exit;
    }
}

// Delete glass
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    pg_query_params($conn, "DELETE FROM glassdescription WHERE glassid = $1", [$delete_id]);
    pg_query_params($conn, "DELETE FROM glass WHERE glassid = $1", [$delete_id]);
    $_SESSION['success'] = "🗑️ Glass deleted successfully!";
    header("Location: manage_glass.php");
    exit;
}

// Fetch all glasses (with optional search)
$search = $_GET['search'] ?? '';
$query = "
    SELECT g.glassid, g.name, g.price, g.material, g.image,
           d.rim, d.shape, d.feature, d.framewidth
    FROM glass g
    LEFT JOIN glassdescription d ON g.glassid = d.glassid
    WHERE g.name ILIKE $1 OR g.material ILIKE $1
    ORDER BY g.glassid ASC
";
$result = pg_query_params($conn, $query, ["%$search%"]);

// Info message if no glasses
if (pg_num_rows($result) == 0) {
    $_SESSION['info'] = "No glasses found. You can add new glasses below!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Glasses</title>
<style>
body { font-family: 'Poppins', sans-serif; margin:0; background: #f4f6f8; }
.navbar {
    background: linear-gradient(135deg, #007bff, #00d4ff);
    color: white; padding: 15px 30px; display:flex; justify-content:space-between; align-items:center;
}
.navbar a { color:white; text-decoration:none; font-weight:bold; margin-left:15px; }
.navbar a:hover { text-decoration:underline; }
.container { max-width:1200px; margin:auto; padding:30px; }
h2 { margin-top:30px; }
table { width:100%; border-collapse:collapse; margin-top:20px; background:white; box-shadow:0 2px 10px rgba(0,0,0,0.1); }
th, td { padding:12px; border-bottom:1px solid #eee; text-align:center; }
th { background:#007bff; color:white; }
button, .btn { background: linear-gradient(135deg,#007bff,#00d4ff); color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; font-weight:bold; }
button:hover, .btn:hover { background: linear-gradient(135deg,#0056b3,#009edc); }
.delete-btn { background:#ff4d4d; }
.delete-btn:hover { background:#cc0000; }
.form-control { margin-bottom:10px; }
.form-control input, textarea { width:100%; padding:10px; border-radius:5px; border:1px solid #ccc; }
.message { padding:10px; border-radius:6px; margin:10px 0; }
.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }
.info { background:#cce5ff; color:#004085; }
.product-img { width:80px; height:80px; object-fit:cover; border-radius:8px; }
.badge { background:#ff4757; color:#fff; padding:3px 8px; border-radius:12px; font-size:12px; margin-left:5px; }
.search-bar { margin-bottom:20px; display:flex; gap:10px; }
.search-bar input { flex:1; padding:10px; border-radius:5px; border:1px solid #ccc; }
</style>
</head>
<body>

<div class="navbar">
    <h1>Manage Glasses</h1>
    <div>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">

    <!-- Flash Messages -->
    <?php if($success) echo "<div class='message success'>$success</div>"; ?>
    <?php if($error) echo "<div class='message error'>$error</div>"; ?>
    <?php if(isset($_SESSION['info'])) { echo "<div class='message info'>{$_SESSION['info']}</div>"; unset($_SESSION['info']); } ?>

    <!-- Search -->
    <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search by name or material" value="<?= htmlspecialchars($search) ?>">
        <button type="submit">Search</button>
    </form>

    <!-- Add New Glass Form -->
    <h2>Add New Glass</h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-control"><input type="text" name="name" placeholder="Glass Name" required></div>
        <div class="form-control"><input type="number" step="0.01" name="price" placeholder="Price" required></div>
        <div class="form-control"><input type="text" name="material" placeholder="Material" required></div>
        <div class="form-control"><input type="text" name="rim" placeholder="Rim Type"></div>
        <div class="form-control"><input type="text" name="shape" placeholder="Shape"></div>
        <div class="form-control"><textarea name="feature" placeholder="Features" rows="2"></textarea></div>
        <div class="form-control"><input type="text" name="framewidth" placeholder="Frame Width"></div>
        <div class="form-control"><input type="file" name="image" accept="image/*"></div>
        <button type="submit" name="add_glass">Add Glass</button>
    </form>

    <!-- Glass Table -->
    <h2>All Glasses</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Image</th>
            <th>Name</th>
            <th>Price</th>
            <th>Material</th>
            <th>Rim</th>
            <th>Shape</th>
            <th>Feature</th>
            <th>Frame Width</th>
            <th>Actions</th>
        </tr>
        <?php while($row = pg_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['glassid'] ?></td>
            <td>
                <?php
                $image_path = "uploads/categories/" . htmlspecialchars($row['image']);
                if(!empty($row['image']) && file_exists($image_path)):
                ?>
                    <img src="<?= $image_path ?>" class="product-img" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php else: ?>
                    <img src="uploads/categories/placeholder.png" class="product-img" alt="No image">
                <?php endif; ?>
            </td>
            <td>
                <?= htmlspecialchars($row['name']) ?>
                <?php if($row['price'] > 1000) echo "<span class='badge'>Premium</span>"; ?>
            </td>
            <td>₹<?= number_format($row['price'],2) ?></td>
            <td><?= htmlspecialchars($row['material']) ?></td>
            <td><?= htmlspecialchars($row['rim']) ?></td>
            <td><?= htmlspecialchars($row['shape']) ?></td>
            <td><?= htmlspecialchars($row['feature']) ?></td>
            <td><?= htmlspecialchars($row['framewidth']) ?></td>
            <td>
                <a href="edit_glass.php?id=<?= $row['glassid'] ?>" class="btn">Edit</a>
                <a href="?delete_id=<?= $row['glassid'] ?>" onclick="return confirm('Are you sure?');">
                    <button type="button" class="delete-btn">Delete</button>
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

</div>
</body>
</html>
