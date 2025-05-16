<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['user_id'] ?? null;
// --- Handle Delete (Review or Message)
if (isset($_GET['delete_review'])) {
    $id = (int)$_GET['delete_review'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("ii", $id, $agent_id);
    $stmt->execute();
    header("Location: client-interaction.php");
    exit;
}

if (isset($_GET['delete_message'])) {
    $id = (int)$_GET['delete_message'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("ii", $id, $agent_id);
    $stmt->execute();
    header("Location: client-interaction.php");
    exit;
}

// --- Handle Reply (Review or Message)
if (isset($_POST['reply_review'])) {
    $reply = trim($_POST['reply_text']);
    $id = (int)$_POST['review_id'];
    $stmt = $conn->prepare("UPDATE reviews SET reply = ? WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("sii", $reply, $id, $agent_id);
    $stmt->execute();
    header("Location: client-interaction.php");
    exit;
}

if (isset($_POST['reply_message'])) {
    $reply = trim($_POST['reply_text']);
    $id = (int)$_POST['message_id'];
    $stmt = $conn->prepare("UPDATE messages SET reply = ? WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("sii", $reply, $id, $agent_id);
    $stmt->execute();
    header("Location: client-interaction.php");
    exit;
}

// --- Fetch Reviews
$review_stmt = $conn->prepare("
    SELECT r.id, r.message, r.sent_at, r.reply, u.name AS client_name
    FROM reviews r
    JOIN users u ON r.client_id = u.id
    WHERE r.agent_id = ?
    ORDER BY r.sent_at DESC
");
$review_stmt->bind_param("i", $agent_id);
$review_stmt->execute();
$review_result = $review_stmt->get_result();

// --- Fetch Messages
$message_stmt = $conn->prepare("
    SELECT m.id, m.message, m.sent_at, m.reply, u.name AS client_name
    FROM messages m
    JOIN users u ON m.client_id = u.id
    WHERE m.agent_id = ?
    ORDER BY m.sent_at DESC
");
$message_stmt->bind_param("i", $agent_id);
$message_stmt->execute();
$message_result = $message_stmt->get_result();
?>

<section>
<div class="container">
    <h2>Client Reviews</h2>
    <div class="card-grid">
        <?php if ($review_result->num_rows > 0): ?>
            <?php while ($row = $review_result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-body">
                        <p><strong>👤 Client:</strong> <?= htmlspecialchars($row['client_name']) ?></p>
                        <p><strong>💬 Review:</strong> <?= htmlspecialchars($row['message']) ?></p>
                        <p>
                            <?php if ($row['reply']): ?>
                                <strong>🟢 Reply:</strong> <?= htmlspecialchars($row['reply']) ?>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="review_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="reply_text" placeholder="Type reply..." required>
                                    <button type="submit" name="reply_review">Reply</button>
                                </form>
                            <?php endif; ?>
                        </p>
                        <p><strong>📅 Date:</strong> <?= $row['sent_at'] ?></p>
                        <p>
                            <a href="?delete_review=<?= $row['id'] ?>" onclick="return confirm('Delete this review?')" class="btn-delete" style="color:red;">🗑️ Delete</a>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No reviews found.</p>
        <?php endif; ?>
    </div>

    <h2>Client Messages</h2>
    <div class="card-grid">
        <?php if ($message_result->num_rows > 0): ?>
            <?php while ($row = $message_result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-body">
                        <p><strong>👤 Client:</strong> <?= htmlspecialchars($row['client_name']) ?></p>
                        <p><strong>📩 Message:</strong> <?= htmlspecialchars($row['message']) ?></p>
                        <p>
                            <?php if ($row['reply']): ?>
                                <strong>🟢 Reply:</strong> <?= htmlspecialchars($row['reply']) ?>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="message_id" value="<?= $row['id'] ?>">
                                    <input type="text" name="reply_text" placeholder="Type reply..." required>
                                    <button type="submit" name="reply_message">Reply</button>
                                </form>
                            <?php endif; ?>
                        </p>
                        <p><strong>📅 Date:</strong> <?= $row['sent_at'] ?></p>
                        <p>
                            <a href="?delete_message=<?= $row['id'] ?>" onclick="return confirm('Delete this message?')" class="btn-delete" style="color:red;">🗑️ Delete</a>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
</div>
</section>

<?php include '../includes/footer.php'; ?>
