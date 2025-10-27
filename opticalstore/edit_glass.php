<?php
include('includes.php');
session_start();

// Redirect if not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Ensure glass ID exists
$glass_id = $_GET['id'] ?? null;
if (!$glass_id) {
    header("Location: select_category.php");
    exit;
}

// Fetch glass + description data (joined)
$query = "
    SELECT g.glassid, g.name, g.price, g.material,
           d.descriptionid, d.rim, d.shape, d.feature, d.framewidth
    FROM glass g
    LEFT JOIN glassdescription d ON g.glassid = d.glassid
    WHERE g.glassid = $1
";
$result = pg_query_params($conn, $query, [$glass_id]);
$glass = pg_fetch_assoc($result);

if (!$glass) {
    die("❌ Glass not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_glass'])) {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $material = trim($_POST['material']);
    $rim = trim($_POST['rim']);
    $shape = trim($_POST['shape']);
    $feature = trim($_POST['feature']);
    $framewidth = trim($_POST['framewidth']);

    // Update glass
    pg_query_params($conn, "
        UPDATE glass 
        SET name = $1, price = $2, material = $3 
        WHERE glassid = $4
    ", [$name, $price, $material, $glass_id]);

    // Update or insert glassdescription
    $check_desc = pg_query_params($conn, "SELECT descriptionid FROM glassdescription WHERE glassid = $1", [$glass_id]);
    if (pg_num_rows($check_desc) > 0) {
        pg_query_params($conn, "
            UPDATE glassdescription 
            SET rim = $1, shape = $2, feature = $3, framewidth = $4 
            WHERE glassid = $5
        ", [$rim, $shape, $feature, $framewidth, $glass_id]);
    } else {
        pg_query_params($conn, "
            INSERT INTO glassdescription (glassid, rim, shape, feature, framewidth)
            VALUES ($1, $2, $3, $4, $5)
        ", [$glass_id, $rim, $shape, $feature, $framewidth]);
    }

    $success = "✅ Glass updated successfully!";
    // Refresh the record data
    $result = pg_query_params($conn, $query, [$glass_id]);
    $glass = pg_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Glass | Optical Store Admin</title>
<style>
    body { font-family: 'Poppins', sans-serif; background: #f4f6f8; margin: 0; }
    .navbar {
        background: linear-gradient(135deg, #007bff, #00d4ff);
        color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center;
    }
    .container { max-width: 800px; margin: 30px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h2 { color: #007bff; margin-bottom: 20px; }
    .form-control { margin-bottom: 15px; }
    .form-control label { display: block; font-weight: 600; margin-bottom: 6px; }
    .form-control input, textarea {
        width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
    }
    textarea { resize: vertical; }
    button {
        background: linear-gradient(135deg, #007bff, #00d4ff);
        color: white; border: none; padding: 10px 20px;
        border-radius: 6px; cursor: pointer; font-weight: bold;
    }
    button:hover { background: linear-gradient(135deg, #0056b3, #009edc); }
    .message { padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    .success { background: #d4edda; color: #155724; }
</style>
</head>
<body>

<div class="navbar">
    <h1>👓 Edit Glass</h1>
    <a href="javascript:history.back()" style="color:white;text-decoration:none;">⬅ Back</a>
</div>

<div class="container">
    <?php if (isset($success)) echo "<div class='message success'>$success</div>"; ?>

    <h2>Edit Glass Information</h2>
    <form method="POST">
        <div class="form-control">
            <label>Glass Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($glass['name']) ?>" required>
        </div>
        <div class="form-control">
            <label>Price ($)</label>
            <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($glass['price']) ?>" required>
        </div>
        <div class="form-control">
            <label>Material</label>
            <input type="text" name="material" value="<?= htmlspecialchars($glass['material']) ?>" required>
        </div>
        <div class="form-control">
            <label>Rim Type</label>
            <input type="text" name="rim" value="<?= htmlspecialchars($glass['rim']) ?>">
        </div>
        <div class="form-control">
            <label>Shape</label>
            <input type="text" name="shape" value="<?= htmlspecialchars($glass['shape']) ?>">
        </div>
        <div class="form-control">
            <label>Features</label>
            <textarea name="feature" rows="3"><?= htmlspecialchars($glass['feature']) ?></textarea>
        </div>
        <div class="form-control">
            <label>Frame Width</label>
            <input type="text" name="framewidth" value="<?= htmlspecialchars($glass['framewidth']) ?>">
        </div>

        <button type="submit" name="update_glass">💾 Update Glass</button>
    </form>
<footer style="background-color: #007BFF; color: #ffffff; padding: 30px 20px; font-family: Arial, sans-serif; border-top-left-radius: 10px; border-top-right-radius: 10px;">
<!-- Footer End -->
 <p style="margin-top: 10px; font-size: 12px;">&copy; <?= date('Y') ?> Clarity Store. All Rights Reserved.</p>
</footer>
</div>
</body>
</html>
