<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

// Delete review
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $del = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $del->bind_param("i", $id);
    $del->execute();
}

// Fetch reviews
$reviews = $conn->query("SELECT r.*, c.name AS client_name, a.name AS agent_name 
                         FROM reviews r 
                         JOIN users c ON r.client_id = c.id 
                         JOIN users a ON r.agent_id = a.id 
                         ORDER BY r.sent_at DESC");
?>

<section>
    <h1>Client Reviews / Messages</h1>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Client</th>
                <th>Agent</th>
                <th>Message</th>
                <th>Sent At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $reviews->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['client_name']) ?></td>
                    <td><?= htmlspecialchars($row['agent_name']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
                    <td><?= $row['sent_at'] ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure to delete this review?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
