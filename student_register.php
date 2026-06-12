<?php
session_start();
include 'db.php';
$message = "";

if (isset($_POST['register_student'])) {
    $s_num = $conn->real_escape_string($_POST['student_number']);
    $name = $conn->real_escape_string($_POST['full_name']);
    $bday = $_POST['birthday'];
    $course = $conn->real_escape_string($_POST['course']);
    $section = $conn->real_escape_string($_POST['section']);

    $check = $conn->query("SELECT * FROM students WHERE student_number = '$s_num'");
    
    if ($check->num_rows > 0) {
        $message = "<div style='color:red; margin-bottom:10px;'>Student Number already registered!</div>";
    } else {
        $sql = "INSERT INTO students (student_number, full_name, birthday, course, section) 
                VALUES ('$s_num', '$name', '$bday', '$course', '$section')";
        
        if ($conn->query($sql)) {
            // SET SESSION DATA
            $_SESSION['student_logged_in'] = true;
            $_SESSION['student_name'] = $name;
            $_SESSION['student_number'] = $s_num;
            // IMPORTANT: Save the course so index.php can filter the syllabus!
            $_SESSION['student_course'] = $course; 

            header("Location: index.php");
            exit(); 
        } else {
            $message = "<div style='color:red;'>Error: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration | PUP ESyllabus</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #ffffff; margin: 0; padding: 20px; }
        .reg-card { background: white; padding: 35px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid #eee; border-top: 8px solid #800000; width: 400px; }
        .reg-card h2 { color: #800000; text-align: center; margin-top: 0; }
        label { display: block; font-size: 0.8rem; color: #800000; font-weight: bold; margin-top: 12px; }
        input, select { display: block; width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; background: #fdfdfd; }
        button { width: 100%; padding: 12px; background: #800000; color: #FFD700; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-top: 25px; }
        .footer-link { text-align: center; margin-top: 15px; font-size: 0.9rem; }
        .footer-link a { color: #800000; text-decoration: none; }
    </style>
</head>
<body>
    <div class="reg-card">
        <h2>Register Account</h2>
        <?php echo $message; ?>
        <form method="POST">
            <label>Student Number:</label>
            <input type="text" name="student_number" placeholder="202X-XXXXX-SJ-0" required>
            
            <label>Full Name:</label>
            <input type="text" name="full_name" placeholder="Juan Dela Cruz" required>
            
            <label>Birthday:</label>
            <input type="date" name="birthday" required>
            
            <label>Course:</label>
            <select name="course" required>
                <option value="" disabled selected>Select Course</option>
                <option value="BSIT">BS Information Technology</option>
                <option value="BSHM">BS Hospitality Management</option>
                <option value="BSED">BS Secondary Education</option>
                <option value="BSPSYCH">BS Psychology</option>
            </select>
            
            <label>Section:</label>
            <input type="text" name="section" placeholder="e.g., 3-1" required>
            
            <button type="submit" name="register_student">CREATE ACCOUNT</button>
        </form>
        <div class="footer-link">
            <a href="student_login.php">Back to Login</a>
        </div>
    </div>
</body>
</html>