<?php
session_start(); 
include 'db.php';
$error = "";

if (isset($_POST['student_login'])) {
    $s_num = $conn->real_escape_string($_POST['student_number']);
    $password = $conn->real_escape_string($_POST['password']); 

    // Check the 'password' column and ensure the student is 'Active'
    $query = "SELECT * FROM students WHERE student_number = '$s_num' AND password = '$password' AND status = 'Active'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // --- THE "LOCKER" (SESSION) SETTINGS ---
        $_SESSION['student_logged_in'] = true; 
        $_SESSION['student_name'] = $row['full_name'];
        $_SESSION['student_number'] = $row['student_number']; 
        $_SESSION['student_course'] = $row['course'];
        
        // --- THE SIGNAL FOR THE WELCOME TOAST ---
        $_SESSION['show_welcome'] = true; 
        
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid Student Number or Password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Portal | PUP ESyllabus</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; background: #ffffff; margin: 0;
        }
        .login-card {
            background: white; padding: 40px; border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1); text-align: center;
            border: 1px solid #eee; border-top: 8px solid #800000; width: 350px;
        }
        .login-card h2 { color: #800000; margin: 0; }
        label { display: block; text-align: left; font-size: 0.8rem; color: #800000; font-weight: bold; margin-top: 15px; }
        input {
            display: block; width: 100%; padding: 12px; margin-top: 5px;
            border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; background: #f9f9f9;
        }
        button {
            width: 100%; padding: 12px; background: #800000; color: #FFD700;
            border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 20px;
        }
        .error-msg { color: #cc0000; background: #f8d7da; padding: 10px; border-radius: 5px; margin-top: 10px; font-size: 0.85rem; }
    </style>
</head>
<body>

    <div class="login-card">
        <img src="logo200.png" width="100" alt="PUP Logo">
        <h2>Student Portal</h2>

        <?php if($error != "") { echo "<div class='error-msg'>$error</div>"; } ?>

        <form method="POST">
            <label>Student Number:</label>
            <input type="text" name="student_number" placeholder="202X-XXXXX-SJ-0" required>
            
            <label>Password:</label>
            <input type="password" name="password" placeholder="Enter your password" required>
            
            <button type="submit" name="student_login">ACCESS LIBRARY</button>
        </form>
    </div>

</body>
</html>