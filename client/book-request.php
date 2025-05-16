<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';


$client_id = $_SESSION['user_id'] ?? 0;

// Submit Request
if (isset($_POST['submit'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $note = trim($_POST['note']);

    $stmt = $conn->prepare("INSERT INTO book_requests (client_id, title, author, note, requested_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isss", $client_id, $title, $author, $note);
    $stmt->execute();
    $message = "Book request submitted successfully.";
}

// Fetch client requests
$stmt = $conn->prepare("SELECT * FROM book_requests WHERE client_id = ? ORDER BY requested_at DESC");
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<section>
<h2>Request a Book</h2>

<?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

<form method="POST" style="display:block;">
    <label>Book Title:</label><br>
    <input type="text" name="title" required><br><br>

    <label>Author:</label><br>
    <input type="text" name="author" required><br><br>

    <label>Note:</label><br>
    <textarea name="note" required style="width:100%; height:100px;"></textarea><br><br>

    <button type="submit" name="submit">Request Book</button>
</form>
<br><br>
<h3>Your Book Requests</h3>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th><th>Title</th><th>Author</th><th>Note</th><th>Requested At</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['author']) ?></td>
            <td><?= htmlspecialchars($row['note']) ?></td>
            <td><?= $row['requested_at'] ?></td>
        </tr>
    <?php endwhile; ?>
</table>
</section>
<?php include '../includes/footer.php'; ?>
