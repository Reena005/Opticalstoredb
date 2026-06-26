<?php
include('includes.php'); // connect to PostgreSQL
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_name = trim($_POST['username']); // form field is still "username"
    $password = trim($_POST['password']);

    // Fetch the admin row from database using admin_name
    $query = "SELECT * FROM admins WHERE admin_name = $1";
    $result = pg_query_params($conn, $query, array($admin_name));

    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_assoc($result);

        // Compare entered password with stored password
        // If your DB stores plain text passwords:
        if ($password === $row['password']) {
            $_SESSION['admin'] = $row['admin_name'];
            header("Location: admin_dashboard.php");
            exit;
        } 
        // If your DB stores hashed passwords (recommended):
        // elseif (password_verify($password, $row['password'])) {
        //     $_SESSION['admin'] = $row['admin_name'];
        //     header("Location: admin_dashboard.php");
        //     exit;
        // } 
        else {
            $error = "❌ Invalid password.";
        }
    } else {
        $error = "❌ Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Optical Store</title>
<style>
   
    body {
        font-family: 'Poppins', sans-serif;
        background: url("https://edit.org/img/blog/1qo-customizable-optical-shop-banners-online-free.jpg");
        background-size:cover;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }

    .login-container {
        background: #fff;
        width: 400px;
        padding: 40px 30px;
        border-radius: 15px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .login-container h2 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    .form-control {
        margin-bottom: 20px;
    }

    .form-control input {
        width: 80%;
        padding: 12px 15px;
        border: 1px solid #9ebc75ff;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    .form-control input:focus {
        border-color: #007bff;
        outline: none;
        box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }

    button {
        width: 90%;
        padding: 12px;
        background: linear-gradient(135deg, #007bff, #00d4ff);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        transform: translateY(-2px);
        background: linear-gradient(135deg, #0056b3, #009edc);
    }

    .error {
        color: red;
        text-align: center;
        margin-top: 10px;
    }

    .brand {
        text-align: center;
        font-weight: bold;
     
        margin-bottom: 25px;
        font-size: 22px;
    }
</style>
</head>
<body>
     

<div class="login-container">
    <div class="brand">👓 Optical Store Admin</div>
    <h2>Login</h2>
    <form method="post">
        <div class="form-control">
            <input type="text" name="username" placeholder="Admin Name" required>
        </div>
        <div class="form-control">
            <input type="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit">Login</button>
        <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    </form>
</div>


</body>

</html>
</head>

