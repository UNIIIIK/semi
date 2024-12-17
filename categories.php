<?php
session_start();
include('../includes/connection.php');

// Check admin authentication
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->openConnection();

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'];
        $stmt = $pdo->prepare("INSERT INTO categories (category_name) VALUES (:category_name)");
        $stmt->execute(['category_name' => $category_name]);
        $_SESSION['success'] = "Category added successfully!";
    }

    if (isset($_POST['update_category'])) {
        $category_id = $_POST['category_id'];
        $category_name = $_POST['category_name'];
        $stmt = $pdo->prepare("UPDATE categories SET category_name = :category_name WHERE category_id = :category_id");
        $stmt->execute(['category_name' => $category_name, 'category_id' => $category_id]);
        $_SESSION['success'] = "Category updated successfully!";
    }

    if (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE category_id = :category_id");
        $stmt->execute(['category_id' => $category_id]);
        $_SESSION['success'] = "Category deleted successfully!";
    }
}

// Fetch categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Categories</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- Add Category Form -->
    <form method="POST" class="mb-3">
        <div class="row">
            <div class="col-md-8">
                <input type="text" name="category_name" class="form-control" placeholder="New Category Name" required>
            </div>
            <div class="col-md-4">
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
            </div>
        </div>
    </form>

    <!-- Category List -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['category_id']; ?></td>
                    <td><?= htmlspecialchars($category['category_name']); ?></td>
                    <td>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="category_id" value="<?= $category['category_id']; ?>">
                            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $category['category_id']; ?>">Edit</button>
                            <button type="submit" name="delete_category" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $category['category_id']; ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="category_id" value="<?= $category['category_id']; ?>">
                                    <div class="mb-3">
                                        <label for="category_name" class="form-label">Category Name</label>
                                        <input type="text" name="category_name" class="form-control" value="<?= htmlspecialchars($category['category_name']); ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_category" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
