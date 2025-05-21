<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$client_id = $_SESSION['user_id'] ?? 0;


// --- Add to Wishlist ---
if (isset($_GET['add']) && is_numeric($_GET['add'])) {
    $book_id = intval($_GET['add']);

    // Check if already in wishlist
    $check = $conn->prepare("SELECT id FROM wishlist WHERE client_id = ? AND book_id = ?");
    $check->bind_param("ii", $client_id, $book_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $add = $conn->prepare("INSERT INTO wishlist (client_id, book_id, added_at) VALUES (?, ?, NOW())");
        $add->bind_param("ii", $client_id, $book_id);
        $add->execute();
        $message = "Book added to wishlist.";
    } else {
        $message = "Book already in wishlist.";
    }
}

// --- Remove from Wishlist ---
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $remove_id, $client_id);
    $stmt->execute();
    $message = "Book removed from wishlist.";
}

// --- Add to Cart ---
if (isset($_GET['cart']) && is_numeric($_GET['cart'])) {
    $wishlist_id = intval($_GET['cart']);

    // Get book_id from wishlist
    $stmt = $conn->prepare("SELECT book_id FROM wishlist WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $wishlist_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $book_id = $row['book_id'];

        // Check if already in cart
        $check = $conn->prepare("SELECT id, quantity FROM cart WHERE client_id = ? AND book_id = ?");
        $check->bind_param("ii", $client_id, $book_id);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $cart_row = $check_result->fetch_assoc();
            $new_qty = $cart_row['quantity'] + 1;
            $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $update->bind_param("ii", $new_qty, $cart_row['id']);
            $update->execute();
        } else {
            $insert = $conn->prepare("INSERT INTO cart (client_id, book_id, quantity) VALUES (?, ?, 1)");
            $insert->bind_param("ii", $client_id, $book_id);
            $insert->execute();
        }
        $message = "Book added to cart.";
    }
}



// --- Pagination + Search ---
$limit = 3;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// --- Total Count for Pagination (Search-aware)
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM wishlist 
    JOIN books ON wishlist.book_id = books.id 
    WHERE wishlist.client_id = ? AND books.title LIKE ?
");
$count_stmt->bind_param("is", $client_id, $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

// --- Fetch Wishlist
$stmt = $conn->prepare("
    SELECT wishlist.id, books.title, books.author, books.price 
    FROM wishlist 
    JOIN books ON wishlist.book_id = books.id 
    WHERE wishlist.client_id = ? AND books.title LIKE ?
    ORDER BY wishlist.added_at DESC
    LIMIT ?, ?
");
$stmt->bind_param("isii", $client_id, $searchParam, $start, $limit);
$stmt->execute();
$wishlist = $stmt->get_result();
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
button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    box-sizing: border-box;
}

button {
    cursor: pointer;
    background-color:  #1a2942;
    border: none;
    font-weight: bold;
    color:white;
}

button:hover {
    background-color: #c7a100;
    color: #1a2942;
}
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
            <input type="text" name="search" placeholder="Search books..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <div class="wishlist">
        <h2>My Wishlist</h2>
        <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

        <?php if ($wishlist->num_rows > 0): ?>
            <?php while ($row = $wishlist->fetch_assoc()): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p>Author: <?= htmlspecialchars($row['author']) ?></p>
                    <p>Price: ₹<?= $row['price'] ?></p>
                    <a href="?remove=<?= $row['id'] ?>" onclick="return confirm('Remove this book from wishlist?')">Remove</a>
                    <a href="?cart=<?= $row['id'] ?>">
                        <button>Add to Cart</button>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No books found in wishlist.</p>
        <?php endif; ?>
    </div>

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
