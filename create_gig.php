<?php
session_start();
require_once 'db.php';

// Debug session and role
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Session user_id not set. Please log in again.'); window.location.href='login.php';</script>";
    exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'seller') {
    echo "<script>alert('Access denied: You must be a seller to create a gig. Your role is " . (isset($_SESSION['role']) ? addslashes($_SESSION['role']) : 'not set') . ". Please log in with a seller account or contact support to update your role.'); window.location.href='signup.php';</script>";
    exit;
}

// Debug: Confirm user details
try {
    $stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        echo "<script>alert('User not found in database. Please re-login or contact support.'); window.location.href='logout.php';</script>";
        exit;
    }
} catch (PDOException $e) {
    echo "<script>alert('Database error checking user: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}

// Ensure Uploads directory exists
$upload_dir = 'Uploads/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo "<script>alert('Failed to create Uploads directory. Please check server permissions.');</script>";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form inputs
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $price = floatval($_POST['price'] ?? 0);

    // Check if all required fields are filled
    if (empty($title) || empty($description) || empty($category) || $price <= 0) {
        echo "<script>alert('Please fill all required fields with valid data.');</script>";
    } elseif (!isset($_FILES['image']) || $_FILES['image']['error'] == UPLOAD_ERR_NO_FILE) {
        echo "<script>alert('Please upload an image.');</script>";
    } else {
        // Handle file upload
        $image = $_FILES['image']['name'];
        $target = $upload_dir . basename($image);
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];

        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Only JPEG, PNG, or GIF images are allowed.');</script>";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) { // 5MB limit
            echo "<script>alert('Image size must be less than 5MB.');</script>";
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO gigs (user_id, title, description, category, price, image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['user_id'], $title, $description, $category, $price, $image]);
                echo "<script>alert('Gig created successfully!'); window.location.href='gig_list.php';</script>";
            } catch (PDOException $e) {
                echo "<script>alert('Database error: " . addslashes($e->getMessage()) . "');</script>";
            }
        } else {
            echo "<script>alert('Failed to upload image. Check directory permissions.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Gig - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #1dbf73;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        header h1 {
            margin: 0;
            font-size: 28px;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            margin: 0 10px;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #1dbf73;
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #333;
        }
        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        button {
            background-color: #1dbf73;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #17a35f;
        }
        .form-group {
            margin-bottom: 15px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            header {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Create a New Gig</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="gig_list.php">Browse Gigs</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <h2>Gig Details</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Gig Title</label>
                <input type="text" id="title" name="title" placeholder="Enter gig title" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Describe your gig" required></textarea>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <option value="Graphic Design">Graphic Design</option>
                    <option value="Writing">Writing</option>
                    <option value="Programming">Programming</option>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price ($)</label>
                <input type="number" id="price" name="price" placeholder="Enter price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="image">Gig Image</label>
                <input type="file" id="image" name="image" accept="image/*" required>
            </div>
            <button type="submit">Create Gig</button>
        </form>
    </div>
</body>
</html>
