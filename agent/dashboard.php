<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

// Total clients
$role_client = 'client';
$total_clients_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM users WHERE role = ?");
$total_clients_stmt->bind_param("s", $role_client);
$total_clients_stmt->execute();
$total_clients_result = $total_clients_stmt->get_result();
$total_clients = $total_clients_result->fetch_assoc()['total'];

// Pagination and search setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// Get total book count (for pagination only)
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM books WHERE title LIKE ?");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$total_books = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_books / $limit);

// Get paginated books based on search
$books_stmt = $conn->prepare("SELECT id, title, uploaded_by, uploaded_at FROM books WHERE title LIKE ? ORDER BY uploaded_at DESC LIMIT ?, ?");
$books_stmt->bind_param("sii", $searchParam, $start, $limit);
$books_stmt->execute();
$books_result = $books_stmt->get_result();
?>
<style>
.pagination {
    text-align: center;
    margin-top: 20px;
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

<!-- Agent Dashboard Content -->
<section>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <h2 class="section-title">Agent Dashboard</h2>

    <!-- Quick Stats -->
    <h3 class="section-subtitle">📊 Quick Stats</h3>
    <div class="card-grid">
        <div class="card stat-card">
            <h4>Total Clients</h4>
            <p><?= $total_clients ?></p>
        </div>
        <div class="card stat-card">
            <h4>Total Books Uploaded</h4>
            <p><?= $total_books ?></p>
        </div>
    </div>

    <!-- Recent Book Uploads -->
    <h3 class="section-subtitle">📚 Recent Book Uploads</h3>
    <div class="card-grid">
        <?php if ($books_result->num_rows > 0): ?>
            <?php while ($book = $books_result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-body">
                        <p><strong>📖 Title:</strong> <?= htmlspecialchars($book['title']) ?></p>
                        <p><strong>👤 Uploaded By:</strong> <?= htmlspecialchars($book['uploaded_by']) ?></p>
                        <p><strong>🕒 Uploaded At:</strong> <?= $book['uploaded_at'] ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No books found.</p>
        <?php endif; ?>
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
