<?php
if (!isset($_SESSION['role'])) {
    header("Location: ../index.php");
    exit;
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <?= ($_SESSION['role'] === 'admin') ? "Admin Dashboard" : "Bookstore"; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="../admin/categories.php" class="nav-link">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a href="../admin/books.php" class="nav-link">Books</a>
                    </li>
                    <li class="nav-item">
                        <a href="../admin/sales.php" class="nav-link">Sales Reports</a>
                    </li>
                    <li class="nav-item">
                        <a href="../admin/messages.php" class="nav-link">Messages</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="../user/landing.php" class="nav-link">Browse Books</a>
                    </li>
                    <li class="nav-item">
                        <a href="../user/cart.php" class="nav-link">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a href="../user/contact.php" class="nav-link">Contact Us</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link text-danger">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
