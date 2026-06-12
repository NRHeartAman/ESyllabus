<?php
session_start();

// 1. SECURITY CHECK: Only Admins should see this
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'db.php'; 

// 2. CHECK IF STUDENT NUMBER IS PROVIDED
if (!isset($_GET['s_num']) || empty($_GET['s_num'])) {
    die("Error: Student Number is missing.");
}

$s_num = $conn->real_escape_string($_GET['s_num']);

// 3. GET STUDENT NAME FOR THE TITLE
$student_check = $conn->query("SELECT full_name FROM students WHERE student_number = '$s_num'");
$student_data = $student_check->fetch_assoc();
$display_name = ($student_data) ? $student_data['full_name'] : $s_num;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Activity Log | Admin</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 40px; background: #fdfdfd; color: #333; }
        .container { max-width: 800px; margin: auto; }
        h2 { color: #800000; border-bottom: 2px solid #FFD700; padding-bottom: 10px; }
        .back-btn { text-decoration: none; color: #800000; font-weight: bold; display: inline-block; margin-bottom: 20px; }
        .log-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th, td { padding: 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #800000; color: #FFD700; }
        tr:hover { background: #fff9f9; }
        .empty-msg { text-align: center; padding: 40px; color: #999; font-style: italic; }
    </style>
</head>
<body>
    <div class="container">
        <a href="manage_students.php" class="back-btn">← Back to Student Management</a>
        
        <h2>Activity History for: <?php echo htmlspecialchars($display_name); ?></h2>
        <p style="font-size: 0.9rem; color: #666;">Student ID: <?php echo htmlspecialchars($s_num); ?></p>

        <table class="log-table">
            <thead>
                <tr>
                    <th>Syllabus / Subject Viewed</th>
                    <th>Date & Time of Access</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch the logs
                $query = "SELECT * FROM activity_logs WHERE student_number = '$s_num' ORDER BY viewed_at DESC";
                $logs = $conn->query($query);

                if ($logs && $logs->num_rows > 0) {
                    while($l = $logs->fetch_assoc()) {
                        echo "<tr>
                                <td><strong>" . htmlspecialchars($l['subject_name']) . "</strong></td>
                                <td>" . date('M d, Y - h:i A', strtotime($l['viewed_at'])) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2' class='empty-msg'>This student has not viewed any syllabi yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>