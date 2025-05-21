<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['user_id'] ?? null;

// --- Handle Actions ---

// End Conversation
if (isset($_GET['end_conversation'])) {
    $client_to_end = (int)$_GET['end_conversation'];
    $stmt = $conn->prepare("UPDATE messages SET conversation_status = 'ended' WHERE agent_id = ? AND client_id = ?");
    $stmt->bind_param("ii", $agent_id, $client_to_end);
    $stmt->execute();
    header("Location: client-messages.php");
    exit;
}

// Delete Message
if (isset($_GET['delete_message'])) {
    $id = (int)$_GET['delete_message'];
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("ii", $id, $agent_id);
    $stmt->execute();
    header("Location: client-messages.php");
    exit;
}

// Delete Review
if (isset($_GET['delete_review'])) {
    $id = (int)$_GET['delete_review'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND agent_id = ?");
    $stmt->bind_param("ii", $id, $agent_id);
    $stmt->execute();
    header("Location: client-messages.php");
    exit;
}

// Send Reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_text'])) {
    $client_id = (int)$_POST['client_id'];
    $reply = trim($_POST['reply_text']);

    // Check if conversation is ended
    $check = $conn->prepare("SELECT conversation_status FROM messages WHERE client_id = ? AND agent_id = ? ORDER BY id DESC LIMIT 1");
    $check->bind_param("ii", $client_id, $agent_id);
    $check->execute();
    $status = $check->get_result()->fetch_assoc();

    if (!$status || $status['conversation_status'] !== 'ended') {
        $stmt = $conn->prepare("INSERT INTO messages (agent_id, client_id, sender, message) VALUES (?, ?, 'agent', ?)");
        $stmt->bind_param("iis", $agent_id, $client_id, $reply);
        $stmt->execute();
    }
    header("Location: client-messages.php");
    exit;
}

// --- Pagination & Search ---
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// Count total clients with messages
$count_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT u.id) AS total
    FROM users u 
    JOIN messages m ON u.id = m.client_id
    WHERE m.agent_id = ? AND u.name LIKE ?
");
$count_stmt->bind_param("is", $agent_id, $searchParam);
$count_stmt->execute();
$total_clients = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_clients / $limit);

// Get client IDs for current page
$client_stmt = $conn->prepare("
    SELECT DISTINCT u.id AS client_id, u.name 
    FROM users u 
    JOIN messages m ON u.id = m.client_id
    WHERE m.agent_id = ? AND u.name LIKE ?
    LIMIT ?, ?
");
$client_stmt->bind_param("isii", $agent_id, $searchParam, $start, $limit);
$client_stmt->execute();
$client_result = $client_stmt->get_result();

$clients = [];
while ($row = $client_result->fetch_assoc()) {
    $clients[$row['client_id']] = $row['name'];
}

// Get messages for these clients
$messages_by_client = [];
if (!empty($clients)) {
    $placeholders = implode(',', array_fill(0, count($clients), '?'));
    $types = str_repeat('i', count($clients) + 1);
    $params = array_merge([$agent_id], array_keys($clients));

    $query = "
        SELECT m.*, u.name AS client_name
        FROM messages m
        JOIN users u ON m.client_id = u.id
        WHERE m.agent_id = ? AND m.client_id IN ($placeholders)
        ORDER BY m.client_id, m.sent_at ASC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages_by_client[$row['client_id']]['name'] = $row['client_name'];
        $messages_by_client[$row['client_id']]['messages'][] = $row;
    }
}
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
    <h2>💬 Client Conversations</h2>

    <form method="GET" style="margin-bottom:15px;">
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search clients...">
        <button type="submit">Search</button>
    </form>

    <div class="container" style="display:flex; gap:25px;">
        <?php if (!empty($messages_by_client)): ?>
            <?php foreach ($messages_by_client as $client_id => $data): 
                $messages = $data['messages'];
                $last = end($messages);
                $conversation_ended = $last['conversation_status'] === 'ended';
            ?>
                <div style="border:2px solid #333; padding:15px;">
                    <h3>👤 <?= htmlspecialchars($data['name']) ?></h3>
                    <a href="?end_conversation=<?= $client_id ?>" onclick="return confirm('End conversation with this client?')" style="color:red;">🛑 End Conversation</a>

                    <div style="margin-top:10px;">
                        <?php foreach ($messages as $msg): ?>
                            <div style="margin:10px 0; padding:10px; background:#f9f9f9; border:1px solid #ccc;">
                                <strong><?= $msg['sender'] === 'agent' ? '🟢 Agent' : '🔵 Client' ?>:</strong>
                                <?= htmlspecialchars($msg['message']) ?>
                                <div style="font-size:12px; color:gray;"><?= htmlspecialchars($msg['sent_at']) ?></div>
                                <a href="?delete_message=<?= $msg['id'] ?>" style="color:red;" onclick="return confirm('Delete this message?')">🗑️ Delete</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$conversation_ended): ?>
                        <form method="POST" style="margin-top:15px;">
                            <input type="hidden" name="client_id" value="<?= $client_id ?>">
                            <textarea name="reply_text" rows="3" placeholder="Your reply..." required style="width:100%;"></textarea>
                            <button type="submit">Reply</button>
                        </form>
                    <?php else: ?>
                        <p style="color:gray;">🛑 This conversation is closed.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No messages found.</p>
        <?php endif; ?>
    </div>
<br>
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
