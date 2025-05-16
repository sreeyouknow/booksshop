<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$client_id = $_SESSION['user_id'] ?? 0;

$stmt = $conn->prepare("
    SELECT purchases.purchase_date, books.title, books.author, books.price 
    FROM purchases
    JOIN books ON purchases.book_id = books.id 
    WHERE purchases.client_id = ?
    ORDER BY purchases.purchase_date DESC
");
$stmt->bind_param("i", $client_id);
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

</style>
<section>
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

</section>
<?php include '../includes/footer.php'; ?>
