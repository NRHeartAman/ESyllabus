<?php
session_start();
include 'db.php';

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

$admin_id = $_SESSION['admin_id'] ?? 0; 
$message = "";

// --- HANDLE PROFILE UPDATE ---
if (isset($_POST['update_profile'])) {
    $new_user = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $new_pwd = $_POST['new_password'];
    
    // Check kung ang bagong username ay ginagamit na ng iba
    $check_user = $conn->query("SELECT id FROM admins WHERE username = '$new_user' AND id != '$admin_id'");
    
    if ($check_user && $check_user->num_rows > 0) {
        $message = "<div class='error'>Username already taken by another admin.</div>";
    } else {
        if (!empty($new_pwd)) {
            // May bagong password
            if (strpos($new_pwd, 'PUP') === 0 && preg_match('/[^a-zA-Z0-9]/', $new_pwd)) {
                $sql = "UPDATE admins SET username = '$new_user', full_name = '$full_name', password = '$new_pwd' WHERE id = '$admin_id'";
                if ($conn->query($sql)) {
                    $message = "<div class='success'>Account updated successfully!</div>";
                    $_SESSION['admin_username'] = $new_user; // Sync session
                    $_SESSION['admin_name'] = $full_name;
                }
            } else {
                $message = "<div class='error'>Password must start with 'PUP' and have a special char.</div>";
            }
        } else {
            // Username at Name lang ang update
            $sql = "UPDATE admins SET username = '$new_user', full_name = '$full_name' WHERE id = '$admin_id'";
            if ($conn->query($sql)) {
                $message = "<div class='success'>Profile updated successfully!</div>";
                $_SESSION['admin_username'] = $new_user;
                $_SESSION['admin_name'] = $full_name;
            }
        }
    }
}

// 2. FETCH CURRENT DATA
$query = "SELECT * FROM admins WHERE id = '$admin_id'";
$result = $conn->query($query);
$admin = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : ['username' => '', 'full_name' => ''];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Profile | PUP ESyllabus</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }
        
        /* SIDEBAR (With smaller logo) */
        .sidebar { width: 260px; height: 100vh; background-color: #800000; color: white; position: fixed; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        .sidebar-header { text-align: center; padding: 20px 10px; border-bottom: 1px solid #a04040; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .sidebar-header img { width: 55px; margin-bottom: 10px; }
        .sidebar h2 { font-size: 1rem; margin: 0; color: #FFD700; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #f1f1f1; display: block; border-bottom: 1px solid #901010; transition: 0.3s; }
        .sidebar a.active { background: #600000; border-left: 5px solid #FFD700; color: #FFD700; }
        
        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        h1 { color: #800000; border-bottom: 3px solid #FFD700; padding-bottom: 10px; }
        
        .profile-card { background: white; padding: 30px; border-radius: 8px; border-top: 8px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 500px; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #800000; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; background: #fff; }
        button { background: #800000; color: #FFD700; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 25px; transition: 0.3s; }
        button:hover { background: #a00000; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php"><img src="logo200.png" alt="PUP Logo"></a>
        <h2>PUP ESYLLABUS</h2>
    </div>
    <a href="admin.php">Dashboard</a>
    <a href="manage_students.php">Student Credentials</a>
    <a href="manage_books.php">Related Books</a>
    <a href="register.php">Create Admin</a>
    <a href="profile_admin.php" class="active">Admin Profile</a>
    <a href="logout.php" style="margin-top:auto; background:#600000; text-align:center; color:#FFD700; font-weight:bold; border-top:1px solid #901010; padding: 15px;">Logout</a>
</div>

<div class="main-content">
    <h1>Profile Settings</h1>
    <?php echo $message; ?>

    <div class="profile-card">
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($admin['full_name']); ?>" required>

            <label>Username</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>

            <label>New Password (Leave blank to keep current)</label>
            <input type="password" name="new_password" placeholder="Must start with PUP & special char">
            
            <button type="submit" name="update_profile">SAVE CHANGES</button>
        </form>
    </div>
</div>

</body>
</html>