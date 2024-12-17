<?php
session_start();
include('../includes/connection.php');
include('../includes/navbar.php');

$connection = new Connection();
$pdo = $connection->openConnection();

$sales = [];
$filter = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['filter_date'])) {
        $filter = $_POST['filter_date'];
        $query = "SELECT SUM(total_amount) AS total, DATE(order_date) AS date 
                  FROM orders 
                  WHERE DATE(order_date) = :filter_date AND status = 'completed'
                  GROUP BY DATE(order_date)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['filter_date' => $filter]);
    } elseif (!empty($_POST['filter_month'])) {
        $filter = $_POST['filter_month'];
        $query = "SELECT SUM(total_amount) AS total, MONTH(order_date) AS month, YEAR(order_date) AS year 
                  FROM orders 
                  WHERE MONTH(order_date) = MONTH(:filter_month) AND YEAR(order_date) = YEAR(:filter_month) AND status = 'completed'
                  GROUP BY MONTH(order_date), YEAR(order_date)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['filter_month' => $filter]);
    } elseif (!empty($_POST['filter_year'])) {
        $filter = $_POST['filter_year'];
        $query = "SELECT SUM(total_amount) AS total, YEAR(order_date) AS year 
                  FROM orders 
                  WHERE YEAR(order_date) = :filter_year AND status = 'completed'
                  GROUP BY YEAR(order_date)";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['filter_year' => $filter]);
    }

    $sales = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Sales Reports</h2>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-4">
            <label for="filter_date" class="form-label">Filter by Date</label>
            <input type="date" name="filter_date" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="filter_month" class="form-label">Filter by Month</label>
            <input type="month" name="filter_month" class="form-control">
        </div>
        <div class="col-md-4">
            <label for="filter_year" class="form-label">Filter by Year</label>
            <input type="number" name="filter_year" class="form-control" placeholder="Enter Year (e.g., 2024)">
        </div>
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">Generate Report</button>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Filter</th>
                <th>Total Sales</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= htmlspecialchars($filter); ?></td>
                    <td>$<?= number_format($sale['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
