<?php
session_start();
include 'db.php';

if(isset($_GET['file']) && isset($_GET['subject'])) {
    $file = $_GET['file'];
    $subject = $conn->real_escape_string($_GET['subject']);
    $s_num = $_SESSION['student_number'];
    $s_name = $_SESSION['student_name'];

    // 1. Dagdag +1 sa views counter
    $conn->query("UPDATE subjects SET views = views + 1 WHERE subject_name = '$subject'");

    // 2. I-record sa History/Activity Logs
    $action = "Viewed Syllabus";
    $details = "Opened syllabus for: " . $subject;
    $conn->query("INSERT INTO activity_logs (student_number, student_name, action, details) 
                  VALUES ('$s_num', '$s_name', '$action', '$details')");

    header("Location: " . $file);
    exit();
}
?>