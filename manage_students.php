<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit(); }
include 'db.php'; 
$message = "";

// --- HANDLE STUDENT REGISTRATION ---
if (isset($_POST['save_student'])) {
    $s_num = $conn->real_escape_string($_POST['student_number']);
    $name = $conn->real_escape_string($_POST['full_name']);
    $bday = $_POST['birthday'];
    $pwd = $_POST['password']; 
    $course = isset($_POST['course']) ? $conn->real_escape_string($_POST['course']) : '';

    $startsWithPUP = (strpos($pwd, 'PUP') === 0);
    $hasSpecialChar = preg_match('/[^a-zA-Z0-9]/', $pwd);

    if (!$startsWithPUP) {
        $message = "<div class='error'>Error: Password must start with 'PUP'</div>";
    } elseif (!$hasSpecialChar) {
        $message = "<div class='error'>Error: Password must include a special character</div>";
    } elseif (empty($course)) {
        $message = "<div class='error'>Error: Please select a course.</div>";
    } else {
        $pwd_secure = $conn->real_escape_string($pwd);
        $sql = "INSERT INTO students (student_number, full_name, birthday, password, course, status) 
                VALUES ('$s_num', '$name', '$bday', '$pwd_secure', '$course', 'Active')
                ON DUPLICATE KEY UPDATE full_name='$name', password='$pwd_secure', birthday='$bday', course='$course'";
        if ($conn->query($sql)) {
            $message = "<div class='success'>Student Account Saved successfully!</div>";
        }
    }
}

// Logic for Archive, Restore, Delete
if (isset($_GET['archive'])) { $id = (int)$_GET['archive']; $conn->query("UPDATE students SET status = 'Archived' WHERE id = $id"); header("Location: manage_students.php"); exit(); }
if (isset($_GET['restore'])) { $id = (int)$_GET['restore']; $conn->query("UPDATE students SET status = 'Active' WHERE id = $id"); header("Location: manage_students.php"); exit(); }
if (isset($_GET['delete'])) { $id = (int)$_GET['delete']; $conn->query("DELETE FROM students WHERE id = $id"); header("Location: manage_students.php"); exit(); }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Credentials | Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>

        /* 1. Global Resets & Layout */
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }
        
        .sidebar { 
            width: 260px; 
            height: 100vh; 
            background-color: #800000; 
            color: white; 
            position: fixed; 
            display: flex; 
            flex-direction: column; 
            box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            z-index: 1000;
        }

        .sidebar-header { 
            text-align: center; 
            padding: 20px 10px; 
            border-bottom: 1px solid #901010; 
            min-height: 160px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
        }

        .sidebar-header img { 
            width: 55px; 
            height: auto;
            margin-bottom: 10px; 
            cursor: pointer; 
            transition: 0.3s;
        }

        .sidebar h2 { 
            font-size: 1rem; 
            margin: 0; 
            color: #FFD700; 
            letter-spacing: 1px; 
            text-transform: uppercase; 
        }

        .sidebar a { 
            padding: 15px 25px; 
            text-decoration: none; 
            color: #f1f1f1; 
            display: block; 
            transition: 0.2s; 
            border-bottom: 1px solid #901010; 
            border-left: 5px solid transparent; 
        }

        .sidebar a:hover { 
            background-color: #a00000; 
            color: #FFD700; 
        }

        /* Active state */
        .sidebar a.active { 
            background-color: #600000; 
            border-left: 5px solid #FFD700; 
            color: #FFD700; 
        }

        .logout-link { 
            margin-top: auto; 
            background: #600000; 
            text-align: center; 
            color: #FFD700 !important; 
            font-weight: bold; 
            border-left: 5px solid transparent !important;
        }

        /* 5. Main Content Layout */
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: calc(100% - 260px); 
        }

        h1 { color: #800000; border-bottom: 3px solid #FFD700; padding-bottom: 10px; margin-top: 0; }
        h2 { color: #800000; margin-top: 50px; border-bottom: 2px solid #FFD700; padding-bottom: 5px; }
        
        .form-box { background: white; padding: 25px; border-radius: 8px; border-top: 5px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .search-box { width: 100%; padding: 12px; margin-bottom: 20px; border: 2px solid #800000; border-radius: 8px; font-size: 1rem; }
        input, select, button { padding: 12px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #800000; color: #FFD700; border: none; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        th { background-color: #800000; color: #FFD700; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .log-time { font-size: 0.85rem; color: #666; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <a href="admin.php"><img src="logo200.png" alt="PUP Logo"></a>
        <h2>PUP ESYLLABUS</h2>
    </div>
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <a href="admin.php" class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="manage_students.php" class="<?php echo ($current_page == 'manage_students.php') ? 'active' : ''; ?>">Student Credentials</a>
    <a href="manage_books.php" class="<?php echo ($current_page == 'manage_books.php') ? 'active' : ''; ?>">Related Books</a>
    <a href="register.php" class="<?php echo ($current_page == 'register.php') ? 'active' : ''; ?>">Create Admin</a>
    <a href="profile_admin.php" class="<?php echo ($current_page == 'profile_admin.php') ? 'active' : ''; ?>">Admin Profile</a>
    
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="main-content">
    <h1>Student Credentials & Registration</h1>
    <?php echo $message; ?>

    <div class="form-box">
        <h3>Register New Student Account</h3>
        <form method="POST" style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 10px;">
            <input type="text" name="student_number" placeholder="Student Number" required style="width: 180px;">
            <input type="text" name="full_name" placeholder="Full Name" required style="width: 200px;">
            <input type="date" name="birthday" required style="width: 150px;">
            <input type="text" name="password" id="studentPassword" placeholder="Set Password" required style="width: 200px;">
            <select name="course" required style="width: 150px;">
                <option value="" disabled selected>Select Course</option>
                <option value="BSIT">BSIT</option><option value="BSHM">BSHM</option>
                <option value="BSED">BSED</option><option value="BSPSYCH">BSPSYCH</option>
            </select>
            <button type="submit" name="save_student">CREATE ACCOUNT</button>
        </form>
    </div>

    <input type="text" id="studentSearch" class="search-box" placeholder="Search by Student Number, Name, or Course...">
    <table id="studentTable">
        <thead>
            <tr>
                <th>Student Info</th>
                <th>Birthday</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM students ORDER BY status ASC, student_number ASC");
            while($row = $result->fetch_assoc()): ?>
                <tr style="<?php echo ($row['status'] == 'Archived') ? 'opacity: 0.6;' : ''; ?>">
                    <td><strong><?php echo $row['student_number']; ?></strong><br><?php echo htmlspecialchars($row['full_name']); ?> (<?php echo $row['course']; ?>)</td>
                    <td><?php echo date("M d, Y", strtotime($row['birthday'])); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <?php if($row['status'] == 'Active'): ?>
                            <a href="manage_students.php?archive=<?php echo $row['id']; ?>" style="color:orange; font-weight:bold; text-decoration:none;">Archive</a>
                        <?php else: ?>
                            <a href="manage_students.php?restore=<?php echo $row['id']; ?>" style="color:green; font-weight:bold; text-decoration:none;">Restore</a>
                        <?php endif; ?> | 
                        <a href="javascript:void(0)" onclick="confirmDelete(<?php echo $row['id']; ?>)" style="color:red; font-weight:bold; text-decoration:none;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>Student Activity History</h2>
    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>Student</th>
                <th>Action</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $logs = $conn->query("SELECT * FROM activity_logs ORDER BY log_time DESC LIMIT 15");
            if ($logs && $logs->num_rows > 0) {
                while($log = $logs->fetch_assoc()): ?>
                    <tr>
                        <td class="log-time"><?php echo date("M d, h:i A", strtotime($log['log_time'])); ?></td>
                        <td><strong><?php echo htmlspecialchars($log['student_name']); ?></strong><br><small><?php echo htmlspecialchars($log['student_number']); ?></small></td>
                        <td style="color: #28a745; font-weight: bold;"><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                    </tr>
                <?php endwhile;
            } else {
                echo "<tr><td colspan='4' style='text-align:center; color:#999;'>No recent activities.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<script>
// Search
document.getElementById('studentSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelector("#studentTable tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].innerText.toUpperCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
});

// Delete
function confirmDelete(id) {
    Swal.fire({
        title: 'Confirm Deletion',
        text: "This student record will be permanently removed.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#800000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete record'
    }).then((result) => {
        if (result.isConfirmed) { window.location.href = "manage_students.php?delete=" + id; }
    })
}
</script>
</body>
</html>