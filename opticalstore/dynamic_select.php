<?php
include 'includes.php';
session_start();

// Optional: Only allow admin to access
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Clarity Shop - Admin SQL Viewer</title>
<style>
    body { font-family: Arial, sans-serif; background: #e0f7fa; margin: 0; padding: 0; }
    .container { max-width: 1000px; margin: 50px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #0077b6; }
    form { text-align: center; margin-bottom: 20px; }
    textarea { width: 80%; padding: 15px; border-radius: 5px; border: 1px solid #0077b6; font-family: monospace; font-size: 14px; resize: vertical; }
    button { margin-top: 10px; padding: 10px 25px; background-color: #0077b6; color: white; border: none; border-radius: 5px; font-size: 15px; cursor: pointer; }
    button:hover { background-color: #0096c7; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #0077b6; text-align: center; }
    th { background-color: #0077b6; color: white; }
    tr:nth-child(even) { background-color: #f0f8ff; }
    p.error { color: red; text-align: center; font-weight: bold; }
    p.success { color: green; text-align: center; font-weight: bold; }
</style>
</head>
<body>
<div class="container">
    <h2>Clarity Shop Admin SQL Viewer</h2>

    <form method="POST">
        <textarea name="query" rows="5" placeholder="Enter your SQL query here..." required><?php
        if (isset($_POST['query'])) echo htmlspecialchars($_POST['query']);
        ?></textarea><br>
        <button type="submit">Run Query</button>
    </form>

<?php
if (isset($_POST['query'])) {
    $query = trim($_POST['query']);
    $query_upper = strtoupper($query);

    // Allowed statements: SELECT and CREATE VIEW
    if (strpos($query_upper, "SELECT") === 0 || strpos($query_upper, "CREATE VIEW") === 0) {
        $result = pg_query($conn, $query);

        if (!$result) {
            echo "<p class='error'>Error: " . pg_last_error($conn) . "</p>";
        } else {
            // Handle SELECT results
            if (strpos($query_upper, "SELECT") === 0) {
                if (pg_num_rows($result) > 0) {
                    $num_fields = pg_num_fields($result);
                    echo "<table><tr>";
                    for ($i = 0; $i < $num_fields; $i++) {
                        echo "<th>" . pg_field_name($result, $i) . "</th>";
                    }
                    echo "</tr>";

                    while ($row = pg_fetch_assoc($result)) {
                        echo "<tr>";
                        foreach ($row as $cell) {
                            echo "<td>" . htmlspecialchars($cell) . "</td>";
                        }
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='text-align:center;'>Query executed successfully but returned no rows.</p>";
                }
            } else if (strpos($query_upper, "CREATE VIEW") === 0) {
                echo "<p class='success'>✅ View created successfully!</p>";
            }
        }
    } else {
        echo "<p class='error'>Only SELECT queries or CREATE VIEW statements are allowed!</p>";
    }
}

pg_close($conn);
?>
</div>
</body>
</html>
