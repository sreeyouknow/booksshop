<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

// --- Approve Request ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve']) && isset($_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $update = $conn->prepare("UPDATE book_requests SET status = 'approved' WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();
        $message = "Request approved successfully.";
    }

    // --- Reject Request ---
    if (isset($_POST['reject']) && isset($_POST['request_id'])) {
        $request_id = intval($_POST['request_id']);
        $update = $conn->prepare("UPDATE book_requests SET status = 'rejected' WHERE id = ?");
        $update->bind_param("i", $request_id);
        $update->execute();
        $message = "Request rejected.";
    }
}

// --- Fetch Requests ---
$request_query = $conn->prepare("
    SELECT br.*, c.name AS client_name 
    FROM book_requests br 
    LEFT JOIN users c ON br.client_id = c.id 
    ORDER BY br.requested_at DESC
");
$request_query->execute();
$requests = $request_query->get_result();
?>

<section>
    <h1>📚 Manage Book Requests</h1>
    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

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

</section>

<?php include '../includes/footer.php'; ?>
