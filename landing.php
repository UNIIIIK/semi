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

// Fetch categories
$categoryStmt = $pdo->query("SELECT * FROM categories");
$categories = $categoryStmt->fetchAll();

// Fetch books with optional filters
$sql = "SELECT b.*, c.category_name FROM books b 
        INNER JOIN categories c ON b.category_id = c.category_id WHERE b.stock > 0";
$params = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['search'])) {
        $sql .= " AND b.title LIKE :search";
        $params['search'] = "%" . $_POST['search'] . "%";
    }
    if (!empty($_POST['category_id'])) {
        $sql .= " AND b.category_id = :category_id";
        $params['category_id'] = $_POST['category_id'];
    }
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Browse Books</h2>

    <!-- Search and Filter -->
    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search books..." value="<?= htmlspecialchars($_POST['search'] ?? '') ?>">
        </div>
        <div class="col-md-4">
            <select name="category_id" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['category_id'] ?>" <?= isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
    </form>

    <!-- Book List -->
    <div class="row">
        <?php foreach ($books as $book): ?>
            <div class="col-md-3 mb-4">
                <div class="card">
                    <img src="../<?= $book['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($book['title']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($book['title']) ?></h5>
                        <p class="card-text">Author: <?= htmlspecialchars($book['author']) ?></p>
                        <p class="card-text">Price: $<?= number_format($book['price'], 2) ?></p>
                        <p class="card-text">Stock: <?= $book['stock'] ?></p>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="book_id" value="<?= $book['book_id'] ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
