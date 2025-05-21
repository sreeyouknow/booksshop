<?php
include '../client/include/header.php';
include '../client/include/sidebar.php';

$client_id = $_SESSION['user_id'] ?? 0;
$message = "";

// --- Remove from Cart ---
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $remove_id, $client_id);
    $stmt->execute();
    $message = "Book removed from cart.";
}

// --- Place Single Book Order ---
if (isset($_POST['place_order']) && isset($_POST['cart_id']) && is_numeric($_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);

    // Get book ID from cart
    $stmt = $conn->prepare("SELECT book_id FROM cart WHERE id = ? AND client_id = ?");
    $stmt->bind_param("ii", $cart_id, $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $book_id = $row['book_id'];
        $insert = $conn->prepare("INSERT INTO purchases (client_id, book_id, purchase_date) VALUES (?, ?, NOW())");
        $insert->bind_param("ii", $client_id, $book_id);
        $insert->execute();

        // Remove from cart after placing order
        $del = $conn->prepare("DELETE FROM cart WHERE id = ? AND client_id = ?");
        $del->bind_param("ii", $cart_id, $client_id);
        $del->execute();

        $message = "Book ordered successfully!";
    } else {
        $message = "Invalid cart item.";
    }
}

// --- Pagination + Search ---
$limit = 3;
$page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchParam = "%{$search}%";

// Count total for pagination
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM cart 
    JOIN books ON cart.book_id = books.id
    WHERE cart.client_id = ? AND books.title LIKE ?
");
$count_stmt->bind_param("is", $client_id, $searchParam);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$total_books = $count_result['total'];
$total_pages = ceil($total_books / $limit);

// --- Get Cart Items with Pagination and Search ---
$stmt = $conn->prepare("
    SELECT cart.id, books.title, books.author, books.price, cart.quantity 
    FROM cart 
    JOIN books ON cart.book_id = books.id 
    WHERE cart.client_id = ? AND books.title LIKE ?
    LIMIT ? OFFSET ?
");
$stmt->bind_param("issi", $client_id, $searchParam, $limit, $start);
$stmt->execute();
$cart = $stmt->get_result();
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
button{
    width: 75%;
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
    <div>
    <h2>🛒 My Cart</h2>
    <?php if (!empty($message)) echo "<p style='color:green;'>$message</p>"; ?>

    <?php if ($cart->num_rows > 0): ?>
        <?php while ($row = $cart->fetch_assoc()): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><strong>Author:</strong> <?= htmlspecialchars($row['author']) ?></p>
            <p><strong>Price:</strong> ₹<?= $row['price'] ?></p>

            <!-- Quantity Dropdown -->
            <label for="quantity_<?= $row['id'] ?>"><strong>Quantity:</strong></label>
            <select name="quantity" id="quantity_<?= $row['id'] ?>">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= $i == $row['quantity'] ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>

            <!-- Remove from Cart -->
            <div>
                <a href="?search=<?= urlencode($search) ?>&page=<?= $page ?>&remove=<?= $row['id'] ?>" onclick="return confirm('Remove this book from cart?')">🗑️ Remove</a>
            </div>

            <!-- Place Single Book Order -->
            <form method="POST">
                <input type="hidden" name="cart_id" value="<?= $row['id'] ?>">
                <button type="submit" name="place_order" onclick="return confirm('Place order for this book?')" style="width:75%;">✅ Place Order</button>
            </form>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
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
