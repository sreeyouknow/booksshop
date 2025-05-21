<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

// --- Approve or Reject Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve'], $_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $update = $conn->prepare("UPDATE book_requests SET status = 'approved' WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();
        $message = "Request approved successfully.";
    }

    if (isset($_POST['reject'], $_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $update = $conn->prepare("UPDATE book_requests SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();
        $message = "Request rejected.";
    }
}

// --- Pagination and Search ---
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// --- Count total requests for pagination ---
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM book_requests 
    WHERE title LIKE ?
");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'] ?? 0;
$total_pages = ceil($total_books / $limit);

// --- Fetch Requests ---
$request_query = $conn->prepare("
    SELECT br.*, c.name AS client_name 
    FROM book_requests br 
    LEFT JOIN users c ON br.client_id = c.id 
    WHERE br.title LIKE ? 
    ORDER BY br.requested_at DESC 
    LIMIT ?, ?
");
$request_query->bind_param("sii", $searchParam, $start, $limit);
$request_query->execute();
$requests = $request_query->get_result();
?>

<section>

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <h1>📚 | Book Requests</h1>
    <?php if (!empty($message)): ?>
        <p style='color:green;'><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <div class="card-grid">
        <?php if ($requests->num_rows > 0): ?>
            <?php while ($request = $requests->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header">
                        <strong>👤 <?= htmlspecialchars($request['client_name'] ?? 'Unknown') ?></strong>
                    </div>
                    <div class="card-body">
                        <p><strong>📖 Book:</strong> <?= htmlspecialchars($request['title']) ?></p>
                        <p><strong>✍️ Author:</strong> <?= htmlspecialchars($request['author']) ?></p>
                        <p><strong>📝 Note:</strong> <?= htmlspecialchars($request['note']) ?></p>
                        <p><strong>⏰ Requested At:</strong> <?= htmlspecialchars($request['requested_at']) ?></p>
                        <p><strong>Status:</strong>
                            <?php if ($request['status'] === 'approved'): ?>
                                <span style="color: green;">Approved</span>
                            <?php elseif ($request['status'] === 'rejected'): ?>
                                <span style="color: red;">Rejected</span>
                            <?php else: ?>
                                <span style="color: orange;">Pending</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="card-actions">
                        <?php if ($request['status'] === 'pending'): ?>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <button type="submit" name="approve" class="btn-approve">✅ Approve</button>
                            </form>
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                                <button type="submit" name="reject" class="btn-reject">❌ Reject</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No book requests found.</p>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next &raquo;</a>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
