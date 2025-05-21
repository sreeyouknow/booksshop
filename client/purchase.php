<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$client_id = $_SESSION['user_id'] ?? 0;

$limit = 3;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1): 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

$count_stmt = $conn->prepare("SELECT COUNT(*) AS total
FROM purchases
JOIN books ON purchases.book_id = books.id
WHERE purchases.client_id = ? AND books.title LIKE ?
");
$count_stmt->bind_param("is", $client_id, $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

$stmt = $conn->prepare("
    SELECT purchases.purchase_date, books.title, books.author, books.price 
    FROM purchases
    JOIN books ON purchases.book_id = books.id 
    WHERE purchases.client_id = ? AND books.title LIKE ?
    ORDER BY purchases.purchase_date DESC LIMIT ? OFFSET ?
");
$stmt->bind_param("issi", $client_id, $searchParam, $limit, $start);
$stmt->execute();
$orders = $stmt->get_result();
?>
<style>
    .order-card-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-top: 20px;
    text-align:left;
}

.order-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    background-color: #f9f9f9;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: transform 0.2s ease;
}

.order-card:hover {
    transform: translateY(-4px);
}

.order-card-body p {
    margin: 6px 0;
    font-size: 15px;
}
.pagination a {
    padding: 6px 12px;
    margin: 2px;
    border: 1px solid #ccc;
    text-decoration: none;
    color: #1a2942;
}
.pagination a.active {
    font-weight: bold;
    background-color: #c7a100;
    color: #fff;
}
</style>
<section>
    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
<h2>My Orders</h2>

<div class="order-card-grid">
    <?php while ($row = $orders->fetch_assoc()): ?>
        <div class="order-card">
            <div class="order-card-body">
                <p><strong>📘 Title:</strong> <?= htmlspecialchars($row['title']) ?></p>
                <p><strong>✍️ Author:</strong> <?= htmlspecialchars($row['author']) ?></p>
                <p><strong>💰 Price:</strong> ₹<?= $row['price'] ?></p>
                <p><strong>📅 Ordered At:</strong> <?= $row['purchase_date'] ?></p>
            </div>
        </div>
    <?php endwhile; ?>
</div>
        <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</section>
<?php include '../includes/footer.php'; ?>
