<?php
session_start();
require_once 'db.php';

$stmt = $pdo->query("SELECT * FROM gigs ORDER BY created_at DESC LIMIT 6");
$gigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiverr Clone - Homepage</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background-color: #1dbf73;
            color: white;
            padding: 20px;
            text-align: center;
        }
        nav a {
            color: white;
            margin: 0 15px;
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .gig-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .gig-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }
        .gig-card:hover {
            transform: translateY(-5px);
        }
        .gig-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        .gig-card h3 {
            margin: 10px;
            font-size: 18px;
        }
        .gig-card p {
            margin: 0 10px 10px;
            color: #555;
        }
        .gig-card .price {
            font-weight: bold;
            color: #1dbf73;
            margin: 10px;
        }
        button {
            background-color: #1dbf73;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #17a35f;
        }
    </style>
</head>
<body>
    <header>
        <h1>Fiverr Clone</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="gig_list.php">Browse Gigs</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Profile</a>
                <a href="create_gig.php">Create Gig</a>
                <a href="messages.php">Messages</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="signup.php">Signup</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <div class="container">
        <h2>Featured Gigs</h2>
        <div class="gig-grid">
            <?php foreach ($gigs as $gig): ?>
                <div class="gig-card">
                    <img src="<?php echo htmlspecialchars($gig['image'] ?? 'default.jpg'); ?>" alt="Gig Image">
                    <h3><?php echo htmlspecialchars($gig['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($gig['description'], 0, 100)) . '...'; ?></p>
                    <p class="price">$<?php echo htmlspecialchars($gig['price']); ?></p>
                    <button onclick="window.location.href='order.php?gig_id=<?php echo $gig['id']; ?>'">Order Now</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
