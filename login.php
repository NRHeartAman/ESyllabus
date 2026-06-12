<?php
session_start();
include 'db.php';

$error = ""; 

if (isset($_POST['login'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $pass = $_POST['password'];

   
    $result = $conn->query("SELECT * FROM admins WHERE username='$user'");
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        
        if (password_verify($pass, $row['password']) || $pass == $row['password']) {
            
            // --- SESSION STORAGE FOR ADMIN ---
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $row['id'];          
            $_SESSION['admin_username'] = $row['username'];
            $_SESSION['admin_name'] = $row['full_name'];  
            
            header("Location: admin.php");
            exit();
        } else { 
            $error = "Invalid password!"; 
        }
    } else { 
        $error = "Admin account not found!"; 
    }
}
?> 
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login | PUP ESyllabus</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background: #ffffff; margin: 0; }
        .login-card { background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center; border: 1px solid #eee; border-top: 8px solid #800000; width: 350px; }
        .login-card img { width: 100px; margin-bottom: 15px; }
        .login-card h2 { color: #800000; margin: 0 0 10px 0; }
        input { display: block; width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; background: #f9f9f9; }
        button { width: 100%; padding: 12px; background: #800000; color: #FFD700; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 1rem; transition: 0.3s; }
        button:hover { background: #600000; box-shadow: 0 4px 10px rgba(128,0,0,0.3); }
        .error-msg { color: #cc0000; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 0.85rem; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="logo200.png" alt="PUP Logo">
        <h2>Admin Login</h2>

        <?php if(!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="login">SIGN IN</button>
        </form>
    </div>
</body>
</html>