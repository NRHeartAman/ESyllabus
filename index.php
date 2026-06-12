<?php
session_start();

// 1. SECURITY CHECK
if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: student_login.php");
    exit();
}

include 'db.php';

// 2. GET STUDENT INFO
$student_course = $_SESSION['student_course'] ?? 'BSIT';
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_number = $_SESSION['student_number'] ?? 'N/A';

// 3. HANDLE VIEWS
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';

// 4. HANDLE DYNAMIC TITLES
$full_course_name = "";
if ($student_course == "BSIT") { $full_course_name = "BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY"; }
elseif ($student_course == "BSHM") { $full_course_name = "BACHELOR OF SCIENCE IN HOSPITALITY MANAGEMENT"; }
elseif ($student_course == "BSED") { $full_course_name = "BACHELOR OF SECONDARY EDUCATION"; }
elseif ($student_course == "BSPSYCH") { $full_course_name = "BACHELOR OF SCIENCE IN PSYCHOLOGY"; }
else { $full_course_name = "PUP CURRICULUM"; }

// SHOW WELCOME TOAST
$show_toast = false;
if (isset($_SESSION['show_welcome'])) {
    $show_toast = true;
    unset($_SESSION['show_welcome']); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | PUP ESyllabus</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }

        /* Sidebar Styles */
        .sidebar { width: 260px; height: 100vh; background-color: #800000; color: white; position: fixed; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.2); z-index: 1000;}
        .sidebar-header { text-align: center; padding: 20px 10px; border-bottom: 1px solid #901010; min-height: 160px; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .sidebar-header img { width: 55px; margin-bottom: 10px; cursor: pointer; }
        .sidebar h2 { font-size: 0.9rem; margin: 0; color: #FFD700; letter-spacing: 1px; text-transform: uppercase; }
        .sidebar a { padding: 15px 25px; text-decoration: none; color: #f1f1f1; display: block; border-bottom: 1px solid #901010; border-left: 5px solid transparent; transition: 0.2s; }
        .sidebar a:hover, .sidebar a.active { background-color: #600000; border-left: 5px solid #FFD700; color: #FFD700; }
        .logout-link { margin-top: auto; background: #600000; text-align: center; color: #FFD700 !important; font-weight: bold; border-left: 5px solid transparent !important; }

        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }

        /* Cinematic Hero Banner */
        @keyframes fadeZoom {
            0% { opacity: 0; transform: scale(1.05); }
            100% { opacity: 1; transform: scale(1); }
        }
        .hero-banner {
            width: 100%; height: 350px; position: relative; overflow: hidden;
            border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 30px;
        }
        .hero-img {
            width: 100%; height: 100%; object-fit: cover;
            background-image: url('Polytechnic_University_of_the_Philippines–San_Juan.jpg');
            background-size: cover; background-position: center;
            animation: fadeZoom 2.5s ease-out forwards;
        }
        .hero-overlay {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: linear-gradient(transparent, rgba(128, 0, 0, 0.9));
            padding: 30px; color: white;
        }

        /* Program Grid Styles */
        .program-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; margin-top: 20px; }
        .program-item { 
            background: #fff; padding: 12px; border-left: 5px solid #FFD700; 
            border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); 
            font-size: 0.85rem; color: #333; transition: 0.3s; 
        }
        .program-item:hover { transform: translateX(8px); background: #f9f9f9; }

        /* Print/Download Button */
        .btn-download { background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-bottom: 20px; }
        
        .content-card { background: white; padding: 30px; border-radius: 8px; border-top: 8px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); line-height: 1.6; }

        /* Curriculum Grid Styles */
        .curriculum-container { background: #fff; padding: 20px; border: 1px solid #ccc; }
        .eval-header { text-align: center; border: 1px solid #000; padding: 10px; margin-bottom: 10px; }
        .eval-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        .year-title { background: #000; color: #fff; font-weight: bold; text-align: center; padding: 5px; text-transform: uppercase; font-size: 0.85rem; }
        .sem-title { background: #4a0000; color: #fff; text-align: center; font-weight: bold; padding: 4px; border: 1px solid #000; font-size: 0.8rem; }
        .eval-table th, .eval-table td { border: 1px solid #000; padding: 4px; font-size: 0.7rem; text-align: center; }
        .desc-link { color: #000; text-decoration: none; display: block; text-align: left; font-weight: 500; }
        .desc-link:hover { text-decoration: underline; color: #800000; }

        #welcomeToast { position: fixed; bottom: 30px; right: 30px; background: #800000; color: #FFD700; padding: 15px 30px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 9999; transform: translateY(120px); opacity: 0; transition: 0.6s; border-left: 5px solid #FFD700; }
        #welcomeToast.show { transform: translateY(0); opacity: 1; }

        @media print {
            .sidebar, .btn-download, .logout-link, #welcomeToast, .hero-banner { display: none !important; }
            .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-header">
            <a href="index.php?view=dashboard"><img src="logo200.png" alt="PUP Logo"></a>
            <h2>PUP ESYLLABUS</h2>
        </div>
        <a href="index.php?view=dashboard" class="<?php echo ($view == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
        <a href="index.php?view=curriculum" class="<?php echo ($view == 'curriculum') ? 'active' : ''; ?>">My Curriculum</a>
        <a href="index.php?view=books" class="<?php echo ($view == 'books') ? 'active' : ''; ?>">Related Books</a>
        <a href="index.php?view=profile" class="<?php echo ($view == 'profile') ? 'active' : ''; ?>">My Profile</a>
        <a href="logout.php" class="logout-link">Logout</a>
    </div>

    <div class="main-content">
        
        <?php if ($view == 'dashboard'): ?>
            <div class="hero-banner">
                <div class="hero-img"></div>
                <div class="hero-overlay">
                    <h2 style="margin:0; font-size: 1.8rem;">PUP San Juan Campus</h2>
                    <p style="margin:5px 0 0 0; opacity: 0.9;">The University of the People</p>
                </div>
            </div>

            <div class="content-card">
                <h2 style="color: #800000; border-bottom: 2px solid #FFD700; padding-bottom: 10px;">Program Offerings</h2>
                <div class="program-grid">
                    <div class="program-item">Bachelor of Science in Accountancy (BSA)</div>
                    <div class="program-item">Bachelor in Secondary Education major in English (BSEDEN)</div>
                    <div class="program-item">Bachelor of Science in Entrepreneurship (BSENT)</div>
                    <div class="program-item">BS in Business Education major in Financial Management (BSBA-FM)</div>
                    <div class="program-item">Bachelor of Science in Information Technology (BSIT)</div>
                    <div class="program-item">Bachelor of Science in Hospitality Management (BSHM)</div>
                    <div class="program-item">Bachelor of Science in Psychology (BS Psy)</div>
                    <div class="program-item">Diploma in Information and Communications Technology (DICT)</div>
                </div>

                <div style="margin-top: 30px;">
                    <h3 style="color: #800000;">History & Legacy</h3>
                    <p>The Polytechnic University of the Philippines San Juan Campus serves as a vital branch in providing quality polytechnic education. Founded to cater to the demand for accessible education, it continues the legacy of academic excellence.</p>
                    <a href="#" style="color: #800000; font-weight: bold; text-decoration: none;">View Officials and Staff →</a>
                </div>
            </div>

        <?php elseif ($view == 'curriculum'): ?>
            <h1>My Academic Curriculum</h1>
            <button class="btn-download" onclick="window.print()">Download as PDF</button>

            <div class="curriculum-container">
                <div class="eval-header">
                    <p style="margin:0; font-size:0.7rem;">Polytechnic University of the Philippines</p>
                    <h2 style="margin: 5px 0;"><?php echo $student_course; ?> (<?php echo $full_course_name; ?>)</h2>
                    <h1 style="margin: 0; color: #000; font-size: 1.5rem;">EVALUATION FORM</h1>
                    <p style="margin: 5px 0; font-size: 0.8rem;">Name: <strong><?php echo htmlspecialchars($student_name); ?></strong> | No: <?php echo htmlspecialchars($student_number); ?></p>
                </div>

                <?php
                $years = [1 => 'FIRST YEAR', 2 => 'SECOND YEAR', 3 => 'THIRD YEAR', 4 => 'FOURTH YEAR'];
                foreach ($years as $yr_num => $yr_label): 
                ?>
                    <table class="eval-table">
                        <tr><td colspan="10" class="year-title"><?php echo $yr_label; ?></td></tr>
                        <tr>
                            <td colspan="5" class="sem-title">First Semester</td>
                            <td colspan="5" class="sem-title">Second Semester</td>
                        </tr>
                        <tr>
                            <th style="width:4%;">Grd</th><th style="width:10%;">Code</th><th>Description</th><th style="width:4%;">Lec</th><th style="width:4%;">Lab</th>
                            <th style="width:4%;">Grd</th><th style="width:10%;">Code</th><th>Description</th><th style="width:4%;">Lec</th><th style="width:4%;">Lab</th>
                        </tr>
                        <?php
                        $s1 = $conn->query("SELECT * FROM subjects WHERE year_level = $yr_num AND semester = 1 AND course_code = '$student_course'");
                        $s2 = $conn->query("SELECT * FROM subjects WHERE year_level = $yr_num AND semester = 2 AND course_code = '$student_course'");
                        $sem1_data = ($s1) ? $s1->fetch_all(MYSQLI_ASSOC) : [];
                        $sem2_data = ($s2) ? $s2->fetch_all(MYSQLI_ASSOC) : [];
                        $max_rows = max(count($sem1_data), count($sem2_data));

                        for ($i = 0; $i < $max_rows; $i++): ?>
                            <tr>
                                <td></td>
                                <td><?php echo htmlspecialchars($sem1_data[$i]['subject_code'] ?? '-'); ?></td>
                                <td><?php if(isset($sem1_data[$i])): ?>
                                    <a href="view_logger.php?file=<?php echo urlencode($sem1_data[$i]['file_path']); ?>&subject=<?php echo urlencode($sem1_data[$i]['subject_name']); ?>" target="_blank" class="desc-link"><?php echo htmlspecialchars($sem1_data[$i]['subject_name']); ?></a>
                                <?php endif; ?></td>
                                <td>-</td><td>-</td>
                                <td></td>
                                <td><?php echo htmlspecialchars($sem2_data[$i]['subject_code'] ?? '-'); ?></td>
                                <td><?php if(isset($sem2_data[$i])): ?>
                                    <a href="view_logger.php?file=<?php echo urlencode($sem2_data[$i]['file_path']); ?>&subject=<?php echo urlencode($sem2_data[$i]['subject_name']); ?>" target="_blank" class="desc-link"><?php echo htmlspecialchars($sem2_data[$i]['subject_name']); ?></a>
                                <?php endif; ?></td>
                                <td>-</td><td>-</td>
                            </tr>
                        <?php endfor; ?>
                    </table>
                <?php endforeach; ?>
            </div>

        <?php elseif ($view == 'books'): ?>
            <h1>Reference Library</h1>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                <?php
                $books = $conn->query("SELECT * FROM books WHERE course_category = '$student_course' ORDER BY uploaded_at DESC");
                while($book = $books->fetch_assoc()): ?>
                    <div class="content-card" style="border-top: 5px solid #28a745; padding: 20px;">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <a href="<?php echo $book['file_path']; ?>" target="_blank" style="color:#800000; font-weight:bold; text-decoration:none;">View PDF</a>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php elseif ($view == 'profile'): ?>
            <h1>Student Profile</h1>
            <div class="content-card" style="max-width: 500px;">
                <p><strong>Name:</strong> <?php echo htmlspecialchars($student_name); ?></p>
                <p><strong>Course:</strong> <?php echo htmlspecialchars($student_course); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div id="welcomeToast">Welcome back, <strong><?php echo explode(' ', $student_name)[0]; ?></strong>.</div>

    <script>
        <?php if ($show_toast): ?>
        window.onload = function() {
            const toast = document.getElementById('welcomeToast');
            setTimeout(() => { toast.classList.add('show'); }, 500);
            setTimeout(() => { toast.classList.remove('show'); }, 4500);
        };
        <?php endif; ?>
    </script>
</body>
</html>