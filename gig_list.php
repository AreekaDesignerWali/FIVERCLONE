<?php
session_start();
require_once 'db.php';

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$price_min = $_GET['price_min'] ?? '';
$price_max = $_GET['price_max'] ?? '';

$query = "SELECT * FROM gigs WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND title LIKE ?";
    $params[] = "%$search%";
}
if ($category) {
    $query .= " AND category = ?";
    $params[] = $category;
}
if ($price_min) {
    $query .= " AND price >= ?";
    $params[] = $price_min;
}
if ($price_max) {
    $query .= " AND price <= ?";
    $params[] = $price_max;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$gigs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Gigs - Fiverr Clone</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        header {
            background-color: #1dbf73;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        input, select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #1dbf73;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
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
    </style>
</head>
<body>
    <header>
        <h1>Browse Gigs</h1>
        <a href="index.php" style="color: white;">Home</a>
    </header>
    <div class="container">
        <form class="search-filter">
            <input type="text" name="search" placeholder="Search gigs..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <option value="Graphic Design" <?php echo $category == 'Graphic Design' ? 'selected' : ''; ?>>Graphic Design</option>
                <option value="Writing" <?php echo $category == 'Writing' ? 'selected' : ''; ?>>Writing</option>
                <option value="Programming" <?php echo $category == 'Programming' ? 'selected' : ''; ?>>Programming</option>
            </select>
            <input type="number" name="price_min" placeholder="Min Price" value="<?php echo htmlspecialchars($price_min); ?>">
            <input type="number" name="price_max" placeholder="Max Price" value="<?php echo htmlspecialchars($price_max); ?>">
            <button type="submit">Filter</button>
        </form>
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
