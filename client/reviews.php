<?php
include 'include/header.php';
include 'include/sidebar.php';

$client_id = $_SESSION['user_id'];

// Handle New Review Submission
if (isset($_POST['submit_review'])) {
    $agent_id = $_POST['agent_id'];
    $rating = $_POST['rating'];
    $message = $_POST['review_message'];

    $stmt = $conn->prepare("INSERT INTO reviews (client_id, agent_id, message, rating) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $client_id, $agent_id, $message, $rating);
    $stmt->execute();
    echo "<p style='color:green;'>Review submitted successfully!</p>";
}

// Handle Delete or Edit Review
$review_data = null;
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if ($action == 'delete_review') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND client_id = ?");
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
        echo "<p style='color:red;'>Review deleted successfully!</p>";
    } elseif ($action == 'edit_review') {
        $stmt = $conn->prepare("SELECT * FROM reviews WHERE id = ? AND client_id = ?");
        $stmt->bind_param("ii", $id, $client_id);
        $stmt->execute();
        $review_data = $stmt->get_result()->fetch_assoc();
    }
}

// Handle Review Edit Submission
if (isset($_POST['edit_review'])) {
    $id = $_POST['review_id'];
    $message = $_POST['edit_message'];
    $rating = $_POST['edit_rating'];

    $stmt = $conn->prepare("UPDATE reviews SET message = ?, rating = ? WHERE id = ? AND client_id = ?");
    $stmt->bind_param("siii", $message, $rating, $id, $client_id);
    $stmt->execute();
    echo "<p style='color:green;'>Review updated successfully!</p>";
}
// Fetch client reviews and messages
$review_stmt = $conn->prepare("SELECT * FROM reviews WHERE client_id = ? ORDER BY sent_at DESC");
$review_stmt->bind_param("i", $client_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();

?>
<section>
    <h3>Leave New Review</h3>
    <form method="POST">
        <h3>Agent</h3>
        <small>give reviews here its send from agent</small>
        <input type="hidden" name="agent_id" placeholder="Agent ID"  value="2" required>
        <textarea name="review_message" placeholder="Your review" required></textarea>
        <input type="number" name="rating" min="1" max="5" placeholder="Rating (1-5)" required>
        <button type="submit" name="submit_review">Submit</button>
    </form>
    <hr>
     <!-- Edit Review Form -->
    <?php if ($review_data): ?>
        <h3>Edit Review</h3>
        <form method="POST">
            <input type="hidden" name="review_id" value="<?= $review_data['id']; ?>">
            <textarea name="edit_message" required><?= htmlspecialchars($review_data['message']); ?></textarea>
            <input type="number" name="edit_rating" value="<?= $review_data['rating']; ?>" min="1" max="5" required>
            <button type="submit" name="edit_review">Update Review</button>
        </form>
    <?php endif; ?>
 <hr>
    <h2>Your Reviews</h2>
    <?php if ($reviews_result->num_rows > 0): ?>
        <?php while ($review = $reviews_result->fetch_assoc()): ?>
            <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                <p><strong>Review for Agent <?= $review['agent_id']; ?>:</strong> <?= htmlspecialchars($review['message']); ?></p>
                <p><strong>Rating:</strong> <?= $review['rating']; ?> ⭐</p>
                <p><em>Sent at: <?= $review['sent_at']; ?></em></p>
                <?php if (!empty($review['reply'])): ?>
                    <p style="color:green;"><strong>Agent Reply:</strong> <?= htmlspecialchars($review['reply']); ?></p>
                <?php endif; ?>
                <a href="?action=edit_review&id=<?= $review['id']; ?>">Edit</a> |
                <a href="?action=delete_review&id=<?= $review['id']; ?>" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>
    </section>
    <?php include '../includes/footer.php'; ?>