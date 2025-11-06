<?php
include('includes.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

// Handle form submission using procedure
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_manufacturer'])) {
    $name = trim($_POST['name']);
    $contact_info = trim($_POST['contact_info']);

    if ($name) {
        $query = "CALL add_manufacturer_proc($1, $2)";
        $result = pg_query_params($conn, $query, [$name, $contact_info]);

        if ($result) {
            $success = "✅ Manufacturer added successfully!";
        } else {
            $error = "❌ Failed to add manufacturer: " . pg_last_error($conn);
        }
    } else {
        $error = "❌ Manufacturer name is required.";
    }
}

// Fetch all manufacturers
$manufacturers_result = pg_query($conn, "SELECT * FROM manufacturer ORDER BY manufacturerid");

// Fetch log entries
$log_result = pg_query($conn, "SELECT * FROM manufacturer_log ORDER BY action_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Manufacturer | Optical Store Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
body { font-family: 'Poppins', sans-serif; background: #eef2f7; margin:0; padding:0; }
header { background:#4a90e2; color:white; padding:20px; text-align:center; font-size:24px; font-weight:600; box-shadow:0 4px 6px rgba(0,0,0,0.1);}
.container { max-width:800px; margin:40px auto; background:#fff; padding:30px; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,0.1);}
h2 { text-align:center; margin-bottom:25px; color:#333; }
.form-control { margin-bottom:20px; }
.form-control label { display:block; margin-bottom:8px; font-weight:600; color:#555;}
.form-control input, .form-control textarea { width:100%; padding:12px 15px; border-radius:8px; border:1px solid #ccc; font-size:15px; transition:0.3s; }
.form-control input:focus, .form-control textarea:focus { border-color:#4a90e2; outline:none; box-shadow:0 0 5px rgba(74,144,226,0.3);}
button { background:#4a90e2; color:white; border:none; padding:12px 20px; border-radius:8px; cursor:pointer; font-weight:600; width:100%; font-size:16px; transition:0.3s; }
button:hover { background:#357ABD;}
.message { padding:12px; margin-bottom:20px; border-radius:8px; font-weight:500; text-align:center; }
.success { background:#d4edda; color:#155724; }
.error { background:#f8d7da; color:#721c24; }
table { width:100%; border-collapse: collapse; margin-top:30px; }
table th, table td { border:1px solid #ccc; padding:10px; text-align:left; }
table th { background:#4a90e2; color:white; }
table tr:nth-child(even) { background:#f9f9f9; }
</style>
</head>
<body>

<header>Optical Store Admin Panel</header>
<div class="container">
    <h2>Add Manufacturer</h2>
    <?php if(isset($success)) echo "<div class='message success'>$success</div>"; ?>
    <?php if(isset($error)) echo "<div class='message error'>$error</div>"; ?>

    <form method="POST">
        <div class="form-control">
            <label>Manufacturer Name:</label>
            <input type="text" name="name" placeholder="Enter manufacturer name" required>
        </div>

        <div class="form-control">
            <label>Contact Info:</label>
            <textarea name="contact_info" rows="3" placeholder="Optional contact details"></textarea>
        </div>

        <button type="submit" name="add_manufacturer">Add Manufacturer</button>
    </form>

    <h2>Existing Manufacturers</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Contact Info</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = pg_fetch_assoc($manufacturers_result)) { ?>
                <tr>
                    <td><?= $row['manufacturerid'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['contact_info']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <h2>Manufacturer Logs</h2>
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Manufacturer ID</th>
                <th>Action</th>
                <th>Time</th>
                <th>Name</th>
                <th>Contact Info</th>
            </tr>
        </thead>
        <tbody>
            <?php while($log = pg_fetch_assoc($log_result)) { ?>
                <tr>
                    <td><?= $log['log_id'] ?></td>
                    <td><?= $log['manufacturerid'] ?></td>
                    <td><?= $log['action_type'] ?></td>
                    <td><?= $log['action_time'] ?></td>
                    <td><?= htmlspecialchars($log['name']) ?></td>
                    <td><?= htmlspecialchars($log['contact_info']) ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</div>
</body>
</html>
