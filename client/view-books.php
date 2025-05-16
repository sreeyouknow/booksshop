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
    $check->bind_param("ii", $client_id, $client_id);
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

// --- Fetch Books
$books = $conn->query("SELECT * FROM books");

// --- Fetch Wishlist
$wishlist_result = $conn->prepare("
    SELECT b.title FROM wishlist w
    JOIN books b ON w.book_id = b.id
    WHERE w.client_id = ?
");
$wishlist_result->bind_param("i", $user_id);
$wishlist_result->execute();
$wishlist = $wishlist_result->get_result();

// --- Fetch Cart
$cart_result = $conn->prepare("
    SELECT b.title, c.quantity FROM cart c
    JOIN books b ON c.book_id = b.id
    WHERE c.client_id = ?
");
$cart_result->bind_param("i", $user_id);
$cart_result->execute();
$cart = $cart_result->get_result();
?>
<style>
    /* cart sytle */
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
.card button{
    width: 75%;
    align-items:center;
}
</style>
<section>
<div class="container">
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
</div>
</section>
<?php include '../includes/footer.php'; ?>
