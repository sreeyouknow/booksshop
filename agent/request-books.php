<?php
include '../agent/include/header.php';
include '../agent/include/sidebar.php';

$agent_id = $_SESSION['id'] ?? null;

// Fetch book requests for the logged-in agent
$requests_stmt = $conn->prepare("SELECT br.id, br.client_id, br.title, br.author, br.note, br.requested_at, br.status, u.name AS client_name 
                                FROM book_requests br 
                                JOIN users u ON u.id = br.client_id 
                                WHERE br.status = 'Pending'");
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();

// Handle approval or denial of a request
if (isset($_POST['approve_request'])) {
    $request_id = $_POST['request_id'];
    
    // Update request status to 'Approved'
    $approve_stmt = $conn->prepare("UPDATE book_requests SET status = 'Approved' WHERE id = ?");
    $approve_stmt->bind_param("i", $request_id);
    $approve_stmt->execute();
    
    // Log the action
    $conn->query("INSERT INTO logs (admin_id, action) VALUES ($agent_id, 'Approved book request ID $request_id')");
    
    header("Location: agent_requests.php");
}

if (isset($_POST['deny_request'])) {
    $request_id = $_POST['request_id'];
    
    // Update request status to 'Denied'
    $deny_stmt = $conn->prepare("UPDATE book_requests SET status = 'Denied' WHERE id = ?");
    $deny_stmt->bind_param("i", $request_id);
    $deny_stmt->execute();
    
    // Log the action
    $conn->query("INSERT INTO logs (admin_id, action) VALUES ($agent_id, 'Denied book request ID $request_id')");
    
    header("Location: agent_requests.php");
}

?>
<!-- Display Book Requests -->
<section>
<h2>Book Requests</h2>

    <h3>Pending Requests</h3>
    <table border="1" cellpadding="5">
        <tr>
            <th>Client Name</th>
            <th>Book Title</th>
            <th>Author</th>
            <th>Note</th>
            <th>Request Date</th>
            <th>Actions</th>
        </tr>
        <?php while ($request = $requests_result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($request['client_name']) ?></td>
                <td><?= htmlspecialchars($request['title']) ?></td>
                <td><?= htmlspecialchars($request['author']) ?></td>
                <td><?= htmlspecialchars($request['note']) ?></td>
                <td><?= $request['requested_at'] ?></td>
                <td>
                    <!-- Approve or Deny -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <button type="submit" name="approve_request">Approve</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $request['id'] ?>">
                        <button type="submit" name="deny_request">Deny</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
