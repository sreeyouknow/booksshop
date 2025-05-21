<?php
include 'include/header.php';
include 'include/sidebar.php';

$client_id = $_SESSION['user_id'];
$agent_id = 2; // or fetch dynamically assigned agent

// Send new message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO messages (agent_id, client_id, sender, message) VALUES (?, ?, 'client', ?)");
    $stmt->bind_param("iis", $agent_id, $client_id, $message);
    $stmt->execute();
}

// Fetch conversation
$stmt = $conn->prepare("SELECT * FROM messages WHERE client_id = ? AND agent_id = ? ORDER BY sent_at ASC");
$stmt->bind_param("ii", $client_id, $agent_id);
$stmt->execute();
$result = $stmt->get_result();

$status_stmt = $conn->prepare("SELECT conversation_status FROM messages WHERE client_id = ? AND agent_id = ? ORDER BY id DESC LIMIT 1");
$status_stmt->bind_param("ii", $client_id, $agent_id);
$status_stmt->execute();
$status = $status_stmt->get_result()->fetch_assoc()['conversation_status'] ?? 'open';
?>
<section><h2>Conversation with Agent</h2>
    <div>
        <?php while ($row = $result->fetch_assoc()): ?>
            <p><strong><?= ucfirst($row['sender']) ?>:</strong> <?= htmlspecialchars($row['message']) ?> <em>(<?= $row['sent_at'] ?>)</em></p>
        <?php endwhile; ?>
    </div>

    <?php if ($status === 'open'): ?>
        <form method="POST">
            <textarea name="message" required></textarea><br>
            <button type="submit">Send</button>
        </form>
    <?php else: ?>
        <p><strong>This conversation has been ended by the agent.</strong></p>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
