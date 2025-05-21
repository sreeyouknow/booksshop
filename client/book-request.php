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

$limit = 5;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

$count_stmt = $conn->prepare("SELECT COUNT(*) AS total
FROM book_requests WHERE title LIKE ?");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

// Fetch client requests
$stmt = $conn->prepare("SELECT * FROM book_requests 
    WHERE client_id = ? AND title LIKE ? 
    ORDER BY requested_at DESC 
    LIMIT ?, ?");
$stmt->bind_param("issi", $client_id, $searchParam, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();
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

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search request books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

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
