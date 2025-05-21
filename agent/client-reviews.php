<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['user_id'] ?? null;

$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// Count total reviews matching search
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM reviews r
    JOIN users u ON r.client_id = u.id
    WHERE r.agent_id = ? AND u.name LIKE ?
");
$count_stmt->bind_param("is", $agent_id, $searchParam);
$count_stmt->execute();
$total_clients = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_clients / $limit);

// Fetch reviews with search and pagination
$review_stmt = $conn->prepare("
    SELECT r.id, r.message, r.sent_at, r.reply, u.name AS client_name
    FROM reviews r
    JOIN users u ON r.client_id = u.id
    WHERE r.agent_id = ? AND u.name LIKE ?
    ORDER BY r.sent_at DESC
    LIMIT ?, ?
");
$review_stmt->bind_param("isii", $agent_id, $searchParam, $start, $limit);
$review_stmt->execute();
$review_result = $review_stmt->get_result();
?>
<style>
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
    <div class="container">
        <h2>📝 Client Reviews</h2>

        <form method="GET" style="margin-bottom:15px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search clients...">
            <button type="submit">Search</button>
        </form>

        <div class="card-grid">
            <?php if ($review_result->num_rows > 0): ?>
                <?php while ($row = $review_result->fetch_assoc()): ?>
                    <div class="card" style="border:1px solid #ccc; margin-bottom:15px; padding:15px;">
                        <p><strong>👤 Client:</strong> <?= htmlspecialchars($row['client_name']) ?></p>
                        <p><strong>💬 Review:</strong> <?= htmlspecialchars($row['message']) ?></p>
                        <p><strong>📅 Date:</strong> <?= htmlspecialchars($row['sent_at']) ?></p>
                        <?php if (!empty($row['reply'])): ?>
                            <p><strong>🟢 Agent Reply:</strong> <?= htmlspecialchars($row['reply']) ?></p>
                        <?php endif; ?>
                        <p>
                            <a href="?delete_review=<?= $row['id'] ?>" onclick="return confirm('Delete this review?')" style="color:red;">🗑️ Delete</a>
                        </p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No reviews found.</p>
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

    </div>
</section>

<?php include '../includes/footer.php'; ?>
