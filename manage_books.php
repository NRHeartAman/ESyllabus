<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) { header("Location: login.php"); exit(); }
include 'db.php';
$message = "";

// --- HANDLE BOOK UPLOAD ---
if (isset($_POST['upload_book'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $course = $_POST['course_category'];
    
    $target_dir = "books_library/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }

    $file_name = time() . "_" . basename($_FILES["book_file"]["name"]);
    $target_file = $target_dir . $file_name;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if ($file_type != "pdf" && $file_type != "epub") {
        $message = "<div class='error'>Error: Only PDF & EPUB files are allowed.</div>";
    } else {
        if (move_uploaded_file($_FILES["book_file"]["tmp_name"], $target_file)) {
            $sql = "INSERT INTO books (title, author, course_category, file_path) 
                    VALUES ('$title', '$author', '$course', '$target_file')";
            if ($conn->query($sql)) {
                $message = "<div class='success'>Reference book uploaded successfully!</div>";
            }
        } else {
            $message = "<div class='error'>Error: File upload failed.</div>";
        }
    }
}

// Delete Book Logic
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT file_path FROM books WHERE id = $id");
    if ($row = $res->fetch_assoc()) { 
        if (file_exists($row['file_path'])) { unlink($row['file_path']); } 
    }
    $conn->query("DELETE FROM books WHERE id = $id");
    header("Location: manage_books.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Related Books | Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* 1. Global Resets & Layout */
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #fdfdfd; }
        
        /* 2. Sidebar - Stable position */
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

        /* 3. Sidebar Header - Stable height for logo */
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

        /* 4. Sidebar Links - Border-left buffer to prevent shifting */
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
        
        .form-box { background: white; padding: 25px; border-radius: 8px; border-top: 5px solid #800000; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        .search-box { 
            width: 100%; padding: 12px; margin-bottom: 20px; 
            border: 2px solid #800000; border-radius: 8px; font-size: 1rem;
        }

        input, select, button { padding: 12px; margin: 5px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #800000; color: #FFD700; border: none; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        th { background-color: #800000; color: #FFD700; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; }
        
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
    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
    <a href="admin.php" class="<?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="manage_students.php" class="<?php echo ($current_page == 'manage_students.php') ? 'active' : ''; ?>">Student Credentials</a>
    <a href="manage_books.php" class="<?php echo ($current_page == 'manage_books.php') ? 'active' : ''; ?>">Related Books</a>
    <a href="register.php" class="<?php echo ($current_page == 'register.php') ? 'active' : ''; ?>">Create Admin</a>
    <a href="profile_admin.php" class="<?php echo ($current_page == 'profile_admin.php') ? 'active' : ''; ?>">Admin Profile</a>
    
    <a href="logout.php" class="logout-link">Logout</a>
</div>

<div class="main-content">
    <h1>Related Books Library</h1>
    <?php echo $message; ?>

   <div class="form-box">
    <h3>Upload New Reference Book</h3>
    <form method="POST" enctype="multipart/form-data" style="display: flex; flex-wrap: wrap; align-items: flex-start; gap: 10px;">
        <input type="text" name="title" placeholder="Book Title" required style="width: 220px; margin: 0;">
        <input type="text" name="author" placeholder="Author/Publisher" required style="width: 200px; margin: 0;">
        
        <select name="course_category" required style="width: 150px; margin: 0;">
            <option value="" disabled selected>Course Category</option>
            <option value="BSIT">BSIT</option>
            <option value="BSHM">BSHM</option>
            <option value="BSED">BSED</option>
            <option value="BSPSYCH">BSPSYCH</option>
        </select>

        <input type="file" name="book_file" accept=".pdf,.epub" required style="width: 250px; margin: 0;">

        <button type="submit" name="upload_book">UPLOAD BOOK</button>
    </form>
</div>

    <input type="text" id="bookSearch" class="search-box" placeholder="Search by Title, Author, or Course...">

    <table id="bookTable">
        <thead>
            <tr>
                <th>Book Details</th>
                <th>Course</th>
                <th>Date Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $books = $conn->query("SELECT * FROM books ORDER BY uploaded_at DESC");
            while($b = $books->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($b['title']); ?></strong><br>
                        <small>Author: <?php echo htmlspecialchars($b['author']); ?></small>
                    </td>
                    <td><?php echo $b['course_category']; ?></td>
                    <td><?php echo date("M d, Y", strtotime($b['uploaded_at'])); ?></td>
                    <td>
                        <a href="<?php echo $b['file_path']; ?>" target="_blank" style="color:green; font-weight:bold; text-decoration:none;">View PDF</a> | 
                        <a href="javascript:void(0)" onclick="confirmDeleteBook(<?php echo $b['id']; ?>)" style="color:red; font-weight:bold; text-decoration:none;">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
// 1. LIVE SEARCH FOR BOOKS
document.getElementById('bookSearch').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.querySelector("#bookTable tbody").rows;
    
    for (let i = 0; i < rows.length; i++) {
        let text = rows[i].innerText.toUpperCase();
        rows[i].style.display = text.includes(filter) ? "" : "none";
    }
});

// 2. SWEETALERT DELETE CONFIRMATION (FORMAL VERSION)
function confirmDeleteBook(id) {
    Swal.fire({
        title: 'Confirm Deletion',
        text: "The selected reference book will be permanently removed from the library.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#800000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete file'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "manage_books.php?delete=" + id;
        }
    })
}
</script>

</body>
</html>