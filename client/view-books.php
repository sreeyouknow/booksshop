<?php
include 'include/header.php';
include 'include/sidebar.php';

$client_id = $_SESSION['user_id'] ?? null;
if (!$client_id) {
    echo "Please log in.";
    exit;
}

// --- Handle Wishlist Add
if (isset($_POST['wishlist'])) {
    $book_id = (int)$_POST['book_id'];
    $check = $conn->prepare("SELECT id FROM wishlist WHERE client_id = ? AND book_id = ?");
    $check->bind_param("ii", $client_id, $book_id);
    $check->execute();
    $check->store_result();
    if ($check->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO wishlist (client_id, book_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $client_id, $book_id);
        $stmt->execute();
    }
}

// --- Handle Cart Add
if (isset($_POST['cart'])) {
    $book_id = (int)$_POST['book_id'];
    $check = $conn->prepare("SELECT id, quantity FROM cart WHERE client_id = ? AND book_id = ?");
    $check->bind_param("ii", $client_id, $book_id); 
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $new_qty = $row['quantity'] + 1;
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $row['id']);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (client_id, book_id, quantity) VALUES (?, ?, 1)");
        $stmt->bind_param("ii", $client_id, $book_id);
        $stmt->execute();
    }
}

// --- Pagination Setup
$limit = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// --- Search
$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// --- Total Count for Pagination
$count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM books 
WHERE title LIKE ?");
$count_stmt->bind_param("s", $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

// --- Fetch Books with Limit + Search
$stmt = $conn->prepare("SELECT * FROM books WHERE title LIKE ? 
LIMIT ?, ?");
$stmt->bind_param("sii", $searchParam, $start, $limit);
$stmt->execute();
$books = $stmt->get_result();

// --- Fetch Wishlist
$wishlist_result = $conn->prepare("
    SELECT b.title FROM wishlist w
    JOIN books b ON w.book_id = b.id
    WHERE w.client_id = ?
");
$wishlist_result->bind_param("i", $client_id);
$wishlist_result->execute();
$wishlist = $wishlist_result->get_result();

// --- Fetch Cart
$cart_result = $conn->prepare("
    SELECT b.title, c.quantity FROM cart c
    JOIN books b ON c.book_id = b.id
    WHERE c.client_id = ?
");
$cart_result->bind_param("i", $client_id);
$cart_result->execute();
$cart = $cart_result->get_result();
?>
<style>
.card {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 12px;
    margin: 10px;
    width: 250px;
    display: inline-block;
    vertical-align: top;
}
.card img {
    max-width: 100%;
    height: auto;
}
.card form {
    margin-top: 10px;
}
.card button {
    width: 75%;
    align-items: center;
}
.search-box {
    text-align: center;
    margin-bottom: 20px;
}
.pagination {
    justify-content: center;
    text-align: center;
    margin-top: 20px;
}
.pagination a {
    margin: 0 5px;
    padding: 5px 10px;
    border: 1px solid #333;
    text-decoration: none;
    color: #333;
}
.pagination a.active {
    background-color: #333;
    color: white;
}
</style>

<section>
<div class="container">

    <div class="search-box">
        <form method="GET">
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <h2>Available Books</h2>
    <?php while ($book = $books->fetch_assoc()): ?>
        <div class="card">
            <h3><?= htmlspecialchars($book['title']) ?></h3>
            <p><strong>Author:</strong> <?= htmlspecialchars($book['author']) ?></p>
            <p><strong>Price:</strong> ₹<?= $book['price'] ?></p>
            <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
            <form method="POST">
                <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                <button type="submit" name="wishlist">Wishlist</button>
                <button type="submit" name="cart">Cart</button>
            </form>
        </div>
    <?php endwhile; ?>

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

</div>
</section>
<?php include '../includes/footer.php'; ?>
