<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}

$order_id = $_GET['order_id'] ?? null;
if ($order_id && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $stmt = $pdo->prepare("SELECT buyer_id, seller_id FROM orders WHERE id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    $receiver_id = ($_SESSION['user_id'] == $order['buyer_id']) ? $order['seller_id'] : $order['buyer_id'];

    $stmt = $pdo->prepare("INSERT INTO messages (order_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$order_id, $_SESSION['user_id'], $receiver_id, $message]);
}

if ($order_id) {
    $stmt = $pdo->prepare("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.order_id = ? ORDER BY m.created_at");
    $stmt->execute([$order_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Fiverr Clone</title>
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
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .message {
            border-bottom: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
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
    </style>
</head>
<body>
    <header>
        <h1>Messages</h1>
        <a href="index.php" style="color: white;">Home</a>
    </header>
    <div class="container">
        <?php if ($order_id): ?>
            <h2>Conversation</h2>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <strong><?php echo htmlspecialchars($message['username']); ?>:</strong>
                    <p><?php echo htmlspecialchars($message['message']); ?></p>
                    <small><?php echo $message['created_at']; ?></small>
                </div>
            <?php endforeach; ?>
            <form method="POST">
                <textarea name="message" placeholder="Type your message..." required></textarea>
                <button type="submit">Send</button>
            </form>
        <?php else: ?>
            <p>Select an order to view messages.</p>
        <?php endif; ?>
    </div>
</body>
</html>
