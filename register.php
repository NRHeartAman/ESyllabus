<?php
session_start();
include 'db.php'; 

// 1. SECURITY CHECK: 
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

$message = "";
// Kunin ang kasalukuyang logged-in admin username mula sa session
$current_admin = $_SESSION['admin_username'] ?? '';

// 2. HANDLE NEW ADMIN REGISTRATION
if (isset($_POST['register'])) {
    $user = $conn->real_escape_string($_POST['username']);
    $full_name = $conn->real_escape_string($_POST['full_name']);
    // Password Hashing (Standard for security)
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->query("SELECT * FROM admins WHERE username='$user'");
    
    if ($check && $check->num_rows > 0) {
        $message = "<div class='error'>Username already exists!</div>";
    } else {
        $sql = "INSERT INTO admins (username, full_name, password) VALUES ('$user', '$full_name', '$pass')";
        if ($conn->query($sql)) {
            $message = "<div class='success'>New Admin Account Created Successfully!</div>";
        } else {
            $message = "<div class='error'>Database Error: " . $conn->error . "</div>";
        }
    }
}

// 3. HANDLE ADMIN DELETION
if (isset($_GET['delete_admin'])) {
    $admin_id = (int)$_GET['delete_admin'];
    
    // Hindi pwedeng i-delete ng admin ang sarili niyang account
    $res = $conn->query("SELECT username FROM admins WHERE id = $admin_id");
    if ($res && $row = $res->fetch_assoc()) {
        if ($row['username'] == $current_admin) {
            $message = "<div class='error'>Action Denied: You cannot remove your own account.</div>";
        } else {
            $conn->query("DELETE FROM admins WHERE id = $admin_id");
            header("Location: register.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Management | PUP ESyllabus</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }
        
        .sidebar { width: 260px; height: 100vh; background-color: #800000; color: white; position: fixed; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); }
        .sidebar-header { text-align: center; padding: 20px 10px; border-bottom: 1px solid #a04040; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .sidebar-header img { width: 70px; margin-bottom: 10px; }
        .sidebar h2 { font-size: 1rem; margin: 0; color: #FFD700; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #f1f1f1; display: block; transition: 0.3s; border-bottom: 1px solid #901010; border-left: 5px solid transparent; }
        .sidebar a:hover { background-color: #a00000; color: #FFD700; }
        .sidebar a.active { background-color: #600000; border-left: 5px solid #FFD700; color: #FFD700; }
        .logout-link { margin-top: auto; background: #600000; text-align: center; color: #FFD700 !important; font-weight: bold; border-left: none !important; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        h1 { color: #800000; border-bottom: 3px solid #FFD700; padding-bottom: 10px; margin-top: 0; }
        
        .admin-flex { display: flex; gap: 30px; align-items: flex-start; margin-top: 20px; }
        .form-box { background: white; padding: 25px; border-radius: 8px; border-top: 5px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 350px; }
        .list-box { flex: 1; background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 5px solid #800000; }
        
        input { display: block; width: 100%; padding: 12px; margin: 15px 0; border: 1px solid #ddd; border-radius: 6px; }
        button { width: 100%; padding: 12px; background: #800000; color: #FFD700; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #800000; color: #FFD700; padding: 12px; }
        td { padding: 12px; border-bottom: 1px solid #eee; }
        
        .active-badge { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; margin-left: 10px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php"><img src="logo200.png" alt="PUP Logo"></a>
        <h2>PUP ESYLLABUS</h2>
    </div>
    <a href="admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="manage_students.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_students.php') ? 'active' : ''; ?>">Student Credentials</a>
    <a href="manage_books.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_books.php') ? 'active' : ''; ?>">Related Books</a>
    <a href="register.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'register.php') ? 'active' : ''; ?>">Create Admin</a>
    
    <a href="profile_admin.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'profile_admin.php') ? 'active' : ''; ?>">Admin Profile</a>
    
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="main-content">
    <h1>System Administrators</h1>
    <?php echo $message; ?>

    <div class="admin-flex">
        <div class="form-box">
            <h3>Add New Admin</h3>
            <form method="POST">
                <input type="text" name="full_name" placeholder="Full Name" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="register">REGISTER ADMIN</button>
            </form>
        </div>

        <div class="list-box">
            <h3>Current Admin Accounts</h3>
            <table>
                <thead>
                    <tr>
                        <th>Administrator</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $admins = $conn->query("SELECT * FROM admins ORDER BY username ASC");
                    if($admins) {
                        while($row = $admins->fetch_assoc()):
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                <small style="color:#666;">@<?php echo htmlspecialchars($row['username']); ?></small>
                                <?php if($row['username'] == $current_admin) echo '<span class="active-badge">CURRENTLY LOGGED IN</span>'; ?>
                            </td>
                            <td>
                                <?php if($row['username'] != $current_admin): ?>
                                    <a href="register.php?delete_admin=<?php echo $row['id']; ?>" 
                                       style="color:red; font-weight:bold; text-decoration:none;" 
                                       onclick="return confirm('Remove access for this administrator?')">Remove</a>
                                <?php else: ?>
                                    <span style="color:#bbb;">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; 
                    } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>