<?php
session_start();
include('../includes/connection.php');
include('../includes/navbar.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->openConnection();

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    // Check if book already in cart
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = :user_id AND book_id = :book_id");
    $stmt->execute(['user_id' => $user_id, 'book_id' => $book_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {
        // Update quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = :user_id AND book_id = :book_id");
        $stmt->execute(['user_id' => $user_id, 'book_id' => $book_id]);
    } else {
        // Add to cart
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (:user_id, :book_id, 1)");
        $stmt->execute(['user_id' => $user_id, 'book_id' => $book_id]);
    }
}

// Fetch cart items
$stmt = $pdo->prepare("SELECT c.*, b.title, b.price, b.image FROM cart c 
                       INNER JOIN books b ON c.book_id = b.book_id 
                       WHERE c.user_id = :user_id");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Your Cart</h2>

    <?php if ($cart_items): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td>
                            <img src="../<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="height: 50px;">
                            <?= htmlspecialchars($item['title']) ?>
                        </td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
