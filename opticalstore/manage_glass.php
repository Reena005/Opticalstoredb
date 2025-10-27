<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$cat_id = $_GET['cat_id'] ?? null;
if (!$cat_id) {
    header("Location: select_category.php");
    exit;
}

// Add new glass
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_glass'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $material = trim($_POST['material']);
    $rim = trim($_POST['rim']);
    $shape = trim($_POST['shape']);
    $feature = trim($_POST['feature']);
    $framewidth = trim($_POST['framewidth']);

    if ($name && $price && $material) {
        $insert_glass = pg_query_params($conn,
            "INSERT INTO glass (name, price, material) VALUES ($1, $2, $3) RETURNING glassid",
            [$name, $price, $material]
        );
        $glass_row = pg_fetch_assoc($insert_glass);
        $new_glass_id = $glass_row['glassid'];

        pg_query_params($conn,
            "INSERT INTO glassdescription (glassid, rim, shape, feature, framewidth) VALUES ($1, $2, $3, $4, $5)",
            [$new_glass_id, $rim, $shape, $feature, $framewidth]
        );

        $success = "✅ Glass added successfully!";
    } else {
        $error = "❌ Please fill all required fields.";
    }
}

// Delete glass
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    pg_query_params($conn, "DELETE FROM glassdescription WHERE glassid = $1", [$delete_id]);
    pg_query_params($conn, "DELETE FROM glass WHERE glassid = $1", [$delete_id]);
    $success = "🗑️ Glass deleted successfully!";
}

// Joined query
$query = "
    SELECT g.glassid, g.name, g.price, g.material,
           d.rim, d.shape, d.feature, d.framewidth
    FROM glass g
    LEFT JOIN glassdescription d ON g.glassid = d.glassid
    ORDER BY g.glassid ASC";
$result = pg_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Glass | Category</title>
<style>
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f6f8; }
    .navbar {
        background: linear-gradient(135deg, #007bff, #00d4ff);
        color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;
    }
    .container { max-width: 1200px; margin: auto; padding: 30px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    th, td { padding: 12px; border-bottom: 1px solid #eee; }
    th { background: #007bff; color: white; }
    button, .btn {
        background: linear-gradient(135deg, #007bff, #00d4ff); color: white; border: none; padding: 8px 15px;
        border-radius: 6px; cursor: pointer; font-weight: bold; text-decoration: none;
    }
    button:hover, .btn:hover { background: linear-gradient(135deg, #0056b3, #009edc); }
    .delete-btn { background: #ff4d4d; }
    .delete-btn:hover { background: #cc0000; }
    .form-control { margin-bottom: 10px; }
    .form-control input, textarea {
        width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;
    }
    .message { padding: 10px; border-radius: 6px; margin: 10px 0; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
</style>
</head>
<body>

<div class="navbar">
    <h1>Manage Glass - Category #<?php echo $cat_id; ?></h1>
    <a href="select_category.php" style="color:white;text-decoration:none;">⬅ Back</a>
</div>

<div class="container">
    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

    <h2>Add New Glass</h2>
    <form method="POST">
        <div class="form-control"><input type="text" name="name" placeholder="Glass Name" required></div>
        <div class="form-control"><input type="number" name="price" step="0.01" placeholder="Price" required></div>
        <div class="form-control"><input type="text" name="material" placeholder="Material" required></div>
        <div class="form-control"><input type="text" name="rim" placeholder="Rim Type"></div>
        <div class="form-control"><input type="text" name="shape" placeholder="Shape"></div>
        <div class="form-control"><textarea name="feature" placeholder="Features" rows="2"></textarea></div>
        <div class="form-control"><input type="text" name="framewidth" placeholder="Frame Width"></div>
        <button type="submit" name="add_glass">Add Glass</button>
    </form>

    <h2>All Glasses (Joined View)</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Material</th>
            <th>Rim</th>
            <th>Shape</th>
            <th>Feature</th>
            <th>Frame Width</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = pg_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $row['glassid'] ?></td>
                <td><?= htmlspecialchars($row['name']) ?></td>
                <td>$<?= number_format($row['price'], 2) ?></td>
                <td><?= htmlspecialchars($row['material']) ?></td>
                <td><?= htmlspecialchars($row['rim']) ?></td>
                <td><?= htmlspecialchars($row['shape']) ?></td>
                <td><?= htmlspecialchars($row['feature']) ?></td>
                <td><?= htmlspecialchars($row['framewidth']) ?></td>
                <td>
                    <a href="edit_glass.php?id=<?= $row['glassid'] ?>" class="btn">Edit</a>
                    <a href="?cat_id=<?= $cat_id ?>&delete_id=<?= $row['glassid'] ?>" onclick="return confirm('Are you sure?');">
                        <button type="button" class="delete-btn">Delete</button>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
