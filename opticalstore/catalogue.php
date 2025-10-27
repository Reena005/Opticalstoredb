<?php
include('includes.php');
session_start();

// Redirect if admin not logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Add or update glass description
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_description'])) {
    $glassid = $_POST['glassid'];
    $rim = trim($_POST['rim']);
    $shape = trim($_POST['shape']);
    $feature = trim($_POST['feature']);
    $framewidth = trim($_POST['framewidth']);

    if ($glassid && $rim && $shape && $feature && $framewidth) {
        // Check if description exists
        $check = pg_query_params($conn, "SELECT * FROM glassdescription WHERE glassid=$1", array($glassid));
        if (pg_num_rows($check) > 0) {
            // Update
            $query = "UPDATE glassdescription SET rim=$1, shape=$2, feature=$3, framewidth=$4 WHERE glassid=$5";
            $result = pg_query_params($conn, $query, array($rim, $shape, $feature, $framewidth, $glassid));
        } else {
            // Insert
            $query = "INSERT INTO glassdescription (glassid, rim, shape, feature, framewidth) VALUES ($1, $2, $3, $4, $5)";
            $result = pg_query_params($conn, $query, array($glassid, $rim, $shape, $feature, $framewidth));
        }

        if ($result) {
            $success = "✅ Description saved successfully!";
        } else {
            $error = "❌ Failed to save description: " . pg_last_error($conn);
        }
    } else {
        $error = "❌ All fields are required.";
    }
}

// Fetch all glasses with descriptions
$catalogue_result = pg_query($conn, "
    SELECT g.glassid, g.name, g.price, g.material, gd.rim, gd.shape, gd.feature, gd.framewidth
    FROM glass g
    LEFT JOIN glassdescription gd ON g.glassid = gd.glassid
    ORDER BY g.glassid ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catalogue | Optical Store Admin</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');
    body { font-family: 'Poppins', sans-serif; margin: 0; background: #f4f6f8; }
    .navbar { background: linear-gradient(135deg, #007bff, #00d4ff); color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
    .navbar a { color: white; text-decoration: none; font-weight: bold; margin-left: 15px; }
    .container { padding: 30px; max-width: 1200px; margin: auto; }
    h2 { color: #007bff; }
    .form-control { margin-bottom: 10px; }
    .form-control input, .form-control textarea { width: 100%; padding: 8px; border-radius: 6px; border: 1px solid #ccc; font-size: 14px; }
    textarea { resize: vertical; }
    button { background: linear-gradient(135deg, #007bff, #00d4ff); color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-weight: bold; }
    button:hover { background: linear-gradient(135deg, #0056b3, #009edc); }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    table th, table td { padding: 10px; text-align: left; border-bottom: 1px solid #eee; vertical-align: top; }
    table th { background: #007bff; color: white; }
    .message { margin: 10px 0; padding: 10px; border-radius: 6px; }
    .success { background: #d4edda; color: #155724; }
    .error { background: #f8d7da; color: #721c24; }
    .logout-btn { background: #ff4d4d; padding: 8px 15px; border-radius: 6px; font-weight: bold; text-decoration: none; color: white; }
    .logout-btn:hover { background: #cc0000; }
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
    <h2>Glass Catalogue</h2>

    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

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
            <th>Action</th>
        </tr>
        <?php
        if ($catalogue_result) {
            while ($row = pg_fetch_assoc($catalogue_result)) {
                echo "<tr>
                        <td>{$row['glassid']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['price']}</td>
                        <td>{$row['material']}</td>
                        <td>
                            <form method='post'>
                                <input type='hidden' name='glassid' value='{$row['glassid']}'>
                                <input type='text' name='rim' value='{$row['rim']}' placeholder='Rim'>
                        </td>
                        <td><input type='text' name='shape' value='{$row['shape']}' placeholder='Shape'></td>
                        <td><textarea name='feature' rows='2' placeholder='Feature'>{$row['feature']}</textarea></td>
                        <td><input type='text' name='framewidth' value='{$row['framewidth']}' placeholder='Frame Width'></td>
                        <td><button type='submit' name='save_description'>Save</button></td>
                        </form>
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
