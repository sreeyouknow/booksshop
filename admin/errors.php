<?php
include '../admin/include/header.php';
include '../admin/include/sidebar.php';

$result = $conn->query("SELECT * FROM error_logs ORDER BY created_at DESC");
?>
<section>
<h2>Logged Errors</h2>
<center>
<table border="1" cellpadding="10" style="width:80%;">
    <tr>
        <th>ID</th>
        <th>Type</th>
        <th>Message</th>
        <th>File</th>
        <th>Line</th>
        <th>Time</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['error_type'] ?></td>
        <td><?= htmlspecialchars($row['error_message']) ?></td>
        <td><?= $row['file'] ?></td>
        <td><?= $row['line'] ?></td>
        <td><?= $row['created_at'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
</center>
</section>
<?php include '../includes/footer.php'; ?>
