<?php
session_start();
include('../includes/connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: ../index.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->openConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount) 
                           SELECT user_id, SUM(b.price * c.quantity) 
                           FROM cart c 
                           INNER JOIN books b ON c.book_id = b.book_id 
                           WHERE c.user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);

    $_SESSION['success'] = "Checkout completed!";
    header("Location: landing.php");
    exit;
}
?>
