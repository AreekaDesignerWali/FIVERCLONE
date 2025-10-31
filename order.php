<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo "<script>alert('Please log in to place or view orders.'); window.location.href='login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle order placement
if (isset($_GET['gig_id']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    $gig_id = filter_var($_GET['gig_id'], FILTER_VALIDATE_INT);
    
    if (!$gig_id) {
        echo "<script>alert('Invalid gig ID.'); window.location.href='gig_list.php';</script>";
        exit;
    }

    try {
        // Verify gig exists and get seller_id
        $stmt = $pdo->prepare("SELECT user_id FROM gigs WHERE id = ?");
        $stmt->execute([$gig_id]);
        $gig = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$gig) {
            echo "<script>alert('Gig not found.'); window.location.href='gig_list.php';</script>";
            exit;
        }

        $seller_id = $gig['user_id'];
        $buyer_id = $user_id;

        // Prevent sellers from ordering their own gigs
        if ($role == 'seller' && $seller_id == $user_id) {
            echo "<script>alert('You cannot order your own gig.'); window.location.href='gig_list.php';</script>";
            exit;
        }

        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders (gig_id, buyer_id, seller_id, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$gig_id, $buyer_id, $seller_id]);
        $order_id = $pdo->lastInsertId();
        echo "<script>alert('Order placed successfully!'); window.location.href='messages.php?order_id=$order_id';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error placing order: " . addslashes($e->getMessage()) . "'); window.location.href='gig_list.php';</script>";
        exit;
    }
}

// Handle order status update (for sellers)
if ($role == 'seller' && isset($_POST['update_status']) && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = filter_var($_POST['order_id'], FILTER_VALIDATE_INT);
    $status = $_POST['status'];

    if (!$order_id || !in_array($status, ['pending', 'accepted', 'completed', 'rejected'])) {
        echo "<script>alert('Invalid order ID or status.'); window.location.href='order.php';</script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND seller_id = ?");
        $stmt->execute([$status, $order_id, $user_id]);
        echo "<script>alert('Order status updated successfully!'); window.location.href='order.php';</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Error updating order: " . addslashes($e->getMessage()) . "'); window.location.href='order.php';</script>";
        exit;
    }
}

// Fetch user's orders
try {
    if ($role == 'buyer') {
        $stmt = $pdo->prepare("SELECT o.*, g.title FROM orders o JOIN gigs g ON o.gig_id = g.id WHERE o.buyer_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT o.*, g.title FROM orders o JOIN gigs g ON o.gig_id = g.id WHERE o.seller_id = ?");
        $stmt->execute([$user_id]);
    }
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error fetching orders: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Fiverr Clone</title>
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
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }
        .order-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .order-card h3 {
            margin: 0 0 10px;
            color: #1dbf73;
        }
        button {
            background-color: #1dbf73;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 5px;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #17a35f;
        }
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-right: 10px;
        }
        .order-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 600px) {
            .container {
                margin: 10px;
                padding: 15px;
            }
            button, select {
                width: 100%;
                margin: 5px 0;
            }
            .order-form {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Your Orders</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="gig_list.php">Browse Gigs</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <?php if (isset($_GET['gig_id'])): ?>
            <h2>Place Order</h2>
            <form method="POST">
                <button type="submit" name="place_order">Place Order</button>
            </form>
        <?php endif; ?>
        <h2>Your Orders</h2>
        <?php if (empty($orders)): ?>
            <p>No orders found.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h3><?php echo htmlspecialchars($order['title']); ?></h3>
                    <p>Status: <?php echo htmlspecialchars($order['status']); ?></p>
                    <p>Order ID: <?php echo htmlspecialchars($order['id']); ?></p>
                    <?php if ($role == 'seller'): ?>
                        <form method="POST" class="order-form">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="status">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="accepted" <?php echo $order['status'] == 'accepted' ? 'selected' : ''; ?>>Accepted</option>
                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="rejected" <?php echo $order['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            </select>
                            <button type="submit" name="update_status">Update Status</button>
                        </form>
                    <?php endif; ?>
                    <button onclick="window.location.href='messages.php?order_id=<?php echo $order['id']; ?>'">Message</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
