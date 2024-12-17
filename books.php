<?php
session_start();
include('../includes/connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->openConnection();

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $title = $_POST['title'];
        $author = $_POST['author'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $description = $_POST['description'];

        $image = '';
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
            $image = 'images/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../" . $image);
        }

        $stmt = $pdo->prepare("INSERT INTO books (title, author, category_id, price, stock, description, image) 
                               VALUES (:title, :author, :category_id, :price, :stock, :description, :image)");
        $stmt->execute([
            'title' => $title,
            'author' => $author,
            'category_id' => $category_id,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'image' => $image,
        ]);
        $_SESSION['success'] = "Book added successfully!";
    }

    if (isset($_POST['update_book'])) {
        $book_id = $_POST['book_id'];
        $title = $_POST['title'];
        $author = $_POST['author'];
        $category_id = $_POST['category_id'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $description = $_POST['description'];

        $image = $_POST['existing_image'];
        if (isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '') {
            $image = 'images/' . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], "../" . $image);
        }

        $stmt = $pdo->prepare("UPDATE books 
                               SET title = :title, author = :author, category_id = :category_id, 
                                   price = :price, stock = :stock, description = :description, image = :image
                               WHERE book_id = :book_id");
        $stmt->execute([
            'title' => $title,
            'author' => $author,
            'category_id' => $category_id,
            'price' => $price,
            'stock' => $stock,
            'description' => $description,
            'image' => $image,
            'book_id' => $book_id,
        ]);
        $_SESSION['success'] = "Book updated successfully!";
    }

    if (isset($_POST['delete_book'])) {
        $book_id = $_POST['book_id'];
        $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = :book_id");
        $stmt->execute(['book_id' => $book_id]);
        $_SESSION['success'] = "Book deleted successfully!";
    }
}

// Fetch books and categories
$booksStmt = $pdo->query("SELECT b.*, c.category_name 
                          FROM books b 
                          INNER JOIN categories c ON b.category_id = c.category_id 
                          ORDER BY b.title ASC");
$books = $booksStmt->fetchAll();

$categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY category_name ASC");
$categories = $categoriesStmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Manage Books</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- Add Book Form -->
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookModal">Add Book</button>

    <!-- Book List Table -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['book_id']; ?></td>
                    <td><?= htmlspecialchars($book['title']); ?></td>
                    <td><?= htmlspecialchars($book['author']); ?></td>
                    <td><?= htmlspecialchars($book['category_name']); ?></td>
                    <td>$<?= number_format($book['price'], 2); ?></td>
                    <td><?= $book['stock']; ?></td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $book['book_id']; ?>">Edit</button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="book_id" value="<?= $book['book_id']; ?>">
                            <button type="submit" name="delete_book" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>

                <!-- Edit Book Modal -->
                <div class="modal fade" id="editModal<?= $book['book_id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Book</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="book_id" value="<?= $book['book_id']; ?>">
                                    <input type="hidden" name="existing_image" value="<?= $book['image']; ?>">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($book['title']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="author" class="form-label">Author</label>
                                        <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($book['author']); ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category</label>
                                        <select class="form-select" name="category_id" required>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['category_id']; ?>" <?= $category['category_id'] == $book['category_id'] ? 'selected' : ''; ?>>
                                                    <?= htmlspecialchars($category['category_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" class="form-control" name="price" value="<?= $book['price']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock</label>
                                        <input type="number" class="form-control" name="stock" value="<?= $book['stock']; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($book['description']); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="image" class="form-label">Image</label>
                                        <input type="file" class="form-control" name="image">
                                        <img src="../<?= $book['image']; ?>" alt="Book Image" width="100" class="mt-2">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" name="update_book" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" class="form-control" name="author" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id']; ?>"><?= htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" class="form-control" name="stock" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image</label>
                        <input type="file" class="form-control" name="image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_book" class="btn btn-success">Add Book</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
