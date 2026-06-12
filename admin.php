<?php
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['admin_logged_in'])) { 
    header("Location: login.php"); 
    exit(); 
}

include 'db.php'; 
$message = "";

// --- HANDLE SYLLABUS UPLOAD ---
if (isset($_POST['upload_syllabus'])) {
    $subject = $conn->real_escape_string($_POST['subject_name']);
    $subject_code = $conn->real_escape_string($_POST['subject_code']);
    $course = isset($_POST['course_code']) ? $_POST['course_code'] : '';
    $year = isset($_POST['year_level']) ? $_POST['year_level'] : '';
    $sem = isset($_POST['semester']) ? $_POST['semester'] : '';
    
    if (empty($course) || empty($year) || empty($sem)) {
        $message = "<div class='error'>Please select a Course, Year, and Semester.</div>";
    } else {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); } 

        $file_name = time() . "_" . str_replace(' ', '_', basename($_FILES["syllabus_file"]["name"]));
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["syllabus_file"]["tmp_name"], $target_file)) {
            // Siniguro nating tama ang pagkakasunod ng columns para pumasok sa database
            $sql = "INSERT INTO subjects (subject_name, subject_code, year_level, semester, file_path, course_code) 
                    VALUES ('$subject', '$subject_code', '$year', '$sem', '$target_file', '$course')";
            if ($conn->query($sql)) {
                $message = "<div class='success'>Syllabus for $subject_code uploaded successfully!</div>";
            } else {
                $message = "<div class='error'>Database Error: " . $conn->error . "</div>";
            }
        } else {
            $message = "<div class='error'>File Upload Failed. Check folder permissions.</div>";
        }
    }
}

// --- HANDLE DELETE ---
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT file_path FROM subjects WHERE id = $id");
    if ($row = $res->fetch_assoc()) { 
        if (file_exists($row['file_path'])) { unlink($row['file_path']); } 
    }
    $conn->query("DELETE FROM subjects WHERE id = $id");
    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | PUP ESyllabus</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }
        
        .sidebar { width: 260px; height: 100vh; background-color: #800000; color: white; position: fixed; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); z-index: 1000; }
        .sidebar-header { text-align: center; padding: 20px 10px; border-bottom: 1px solid #9b1313; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .sidebar-header img { width: 55px; height: auto; margin-bottom: 10px; cursor: pointer; transition: 0.3s; }
        .sidebar h2 { font-size: 1rem; margin: 0; color: #FFD700; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #f1f1f1; display: block; transition: 0.2s; border-bottom: 1px solid #901010; border-left: 5px solid transparent; }
        .sidebar a:hover { background-color: #a00000; color: #FFD700; }
        .sidebar a.active { background-color: #600000; border-left: 5px solid #FFD700; color: #FFD700; }
        .logout-link { margin-top: auto; background: #600000; text-align: center; color: #FFD700 !important; font-weight: bold; border-left: 5px solid transparent !important; }

        .main-content { margin-left: 260px; padding: 40px; width: calc(100% - 260px); }
        h1 { color: #800000; border-bottom: 3px solid #FFD700; padding-bottom: 10px; margin-top: 0; }
        
        .form-box { background: white; padding: 25px; border-radius: 8px; border-top: 5px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        input, select, button { padding: 12px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #800000; color: #FFD700; border: none; cursor: pointer; font-weight: bold; transition: 0.3s; }
        button:hover { background: #600000; }

        .search-bar { width: 100%; padding: 12px; border: 2px solid #800000; border-radius: 8px; margin-bottom: 20px; font-size: 1rem; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th { background-color: #800000; color: #FFD700; padding: 15px; text-align: left; position: sticky; top: 0; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        
        .badge-course { background: #eee; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; color: #800000; font-weight: bold; border: 1px solid #800000; }
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
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <a href="admin.php" class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="manage_students.php">Student Credentials</a>
    <a href="manage_books.php">Related Books</a>
    <a href="register.php">Create Admin</a>
    <a href="profile_admin.php">Admin Profile</a>
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="main-content">
    <h1>Syllabus Management</h1>
    <?php echo $message; ?>
    
    <div class="form-box">
        <h3>Upload New Syllabus</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="text" name="subject_code" placeholder="Subject Code (e.g. COMP1)" required style="width: 200px;">
            <input type="text" name="subject_name" placeholder="Subject Title" required style="width: 300px;">
            <select name="course_code" required>
                <option value="" disabled selected>Course</option>
                <option value="BSIT">BSIT</option>
                <option value="BSHM">BSHM</option>
                <option value="BSED">BSED</option>
                <option value="BSPSYCH">BSPSYCH</option>
            </select>
            <select name="year_level" required>
                <option value="" disabled selected>Year</option>
                <option value="1">Year 1</option><option value="2">Year 2</option>
                <option value="3">Year 3</option><option value="4">Year 4</option>
            </select>
            <select name="semester" required>
                <option value="" disabled selected>Sem</option>
                <option value="1">Sem 1</option><option value="2">Sem 2</option>
            </select>
            <input type="file" name="syllabus_file" accept=".pdf" required>
            <button type="submit" name="upload_syllabus">UPLOAD PDF</button>
        </form>
    </div>

    <input type="text" id="adminSearch" class="search-bar" placeholder="Search by subject code, title or course..." onkeyup="filterTable()">

    <table id="syllabusTable">
        <thead>
            <tr>
                <th>Code</th>
                <th>Subject Name</th>
                <th>Course</th>
                <th>Year & Sem</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // In-update natin ang order para makita mo agad yung pinaka-bagong upload sa dulo o ayon sa year
            $result = $conn->query("SELECT * FROM subjects ORDER BY course_code ASC, year_level ASC, semester ASC");
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($row['subject_code'] ?? 'N/A'); ?></code></td>
                        <td><strong><?php echo htmlspecialchars($row['subject_name']); ?></strong></td>
                        <td><span class="badge-course"><?php echo $row['course_code']; ?></span></td>
                        <td>Year <?php echo $row['year_level']; ?>, Sem <?php echo $row['semester']; ?></td>
                        <td>
                            <a href="view_logger.php?file=<?php echo urlencode($row['file_path']); ?>&subject=<?php echo urlencode($row['subject_name']); ?>" target="_blank" style="color:#800000; font-weight:bold; text-decoration:none;">View</a>
                            <a href="admin.php?delete=<?php echo $row['id']; ?>" style="color:red; margin-left:10px; text-decoration:none;" onclick="return confirm('Sigurado ka bang buburahin ito?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; 
            } else { echo "<tr><td colspan='5' style='text-align:center;'>No records found.</td></tr>"; }
            ?>
        </tbody>
    </table>
</div>

<script>
function filterTable() {
    let input = document.getElementById('adminSearch').value.toUpperCase();
    let rows = document.querySelector("#syllabusTable tbody").rows;
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].innerText.toUpperCase();
        rows[i].style.display = text.includes(input) ? "" : "none";
    }
}
</script>

</body>
</html>