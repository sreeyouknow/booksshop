<?php
include 'include/header.php';
include 'include/sidebar.php';

$client_id = $_SESSION['user_id'];


// Handle New Message Submission
if (isset($_POST['send_message'])) {
    $agent_id = $_POST['agent_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (client_id, agent_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $client_id, $agent_id, $message);
    $stmt->execute();
    echo "<p style='color:green;'>Message sent successfully!</p>";
}

$message_stmt = $conn->prepare("SELECT * FROM messages WHERE client_id = ? ORDER BY sent_at DESC");
$message_stmt->bind_param("i", $client_id);
$message_stmt->execute();
$messages_result = $message_stmt->get_result();
?>

<section>
<div class="container">
    <h3>Send New Message</h3>
    <form method="POST">
        <h3>Agent</h3>
        <small>Agent contact here to message </small>
        <input type="hidden" name="agent_id" placeholder="Agent ID" value="2" required>
        <textarea name="message" placeholder="Your message" required></textarea>
        <button type="submit" name="send_message">Send</button>
    </form>
    <hr>
    <h2>📩 Your Messages</h2>
    <?php if ($messages_result->num_rows > 0): ?>
        <?php while ($msg = $messages_result->fetch_assoc()): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <p><strong>Message to Agent <?= $msg['agent_id']; ?>:</strong> <?= htmlspecialchars($msg['message']); ?></p>
                <p><em>Sent at: <?= $msg['sent_at']; ?></em></p>
                <?php if (!empty($msg['reply'])): ?>
                    <p style="color:green;"><strong>Agent Reply:</strong> <?= htmlspecialchars($msg['reply']); ?></p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No messages yet.</p>
    <?php endif; ?>
   
</div>
</section>

<?php include '../includes/footer.php'; ?>
