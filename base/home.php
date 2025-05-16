<?php include '../includes/header.php'; ?>

<?php
// Prepare and execute the query
$books_stmt = $conn->prepare("SELECT * FROM books");
$books_stmt->execute();
$books_result = $books_stmt->get_result(); // fetch result set
?>

<style>
section {
    padding: 20px;
}

h1, h3 {
    text-align: center;
    margin-bottom: 20px;
}

.books-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}

.card {
    background-color: #fff;
    border-radius: 12px;
    border:2px solid;
    padding: 20px;
    width: 280px;
    transition: transform 0.2s;
}

.card p {
    margin: 8px 0;
    font-size: 15px;
}

.card .buy-btn {
    cursor: pointer;
    background-color: #1a2942;
    border: none;
    font-weight: bold;
    padding:5px 10px;
    margin:10px 0;
    border-radius:5px;
    color:white;
}

.card .buy-btn:hover {
    background-color: white;
    color: #1a2942;
}

</style>

<section>
    <h1>📚 BoOkShOp | Books here</h1>
    <h3>Welcome, <?php echo htmlspecialchars($c_user_name); ?>!</h3>

    <div class="books-container">
        <?php while ($book = $books_result->fetch_assoc()): ?>
            <div class="card">
                <p><strong>📖 Title:</strong> <?= htmlspecialchars($book['title']) ?></p>
                <p><strong>✍️ Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
                <p><strong>💰 Price:</strong> ₹<?= htmlspecialchars($book['price']) ?></p>
                <a href="../base/login.php" class="buy-btn">Buy Now</a>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
