<?php include '../includes/header.php'; ?>

<?php
$name = $email = $password = $role = "";
$errors = $e_errors = $p_errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $role = $_POST['role'];

    if ($name == '' || $email == '' || $password == '') {
        $errors[] = 'Please fill all fields.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $e_errors[] = "Invalid email format.";
    }


    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $e_errors[] = "Email already exists.";
    }

    if (!preg_match("/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $p_errors[] = "Password must be at least 8 characters with 1 capital letter, 1 number, and 1 symbol.";
    }

    if (empty($errors) && empty($e_errors) && empty($p_errors)) {
        $hashpassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = $conn->prepare("INSERT INTO users(name, email, password, role) VALUES (?, ?, ?, ?)");
        $sql->bind_param('ssss', $name, $email, $hashpassword, $role);
        $sql->execute();
        header("Location: login.php");
        exit();
    }
}
?>
<style>
    #container{
        margin:6.3% auto;
    }
</style>
<h2 style="text-align:center;">Register here</h2>
<form action="register.php" method="POST">
    <input type="text" name="name" placeholder="Enter your Full name" value="<?= htmlspecialchars($name) ?>" required>
    <?php if (!empty($errors)) echo "<small style='color:red;'>".implode("<br>", $errors)."</small>"; ?>

    <input type="email" name="email" placeholder="Enter your Email" value="<?= htmlspecialchars($email) ?>" required>
    <?php if (!empty($e_errors)) echo "<small style='color:red;'>".implode("<br>", $e_errors)."</small>"; ?>

    <input type="password" name="password" placeholder="Enter your password" required>
    <?php if (!empty($p_errors)) echo "<small style='color:red;'>".implode("<br>", $p_errors)."</small>"; ?>

    <select name="role" id="role" required>
        <option value="client" <?= $role == 'client' ? 'selected' : '' ?>>Client</option>
    </select>

    <button type="submit">Register</button>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</form>

<?php include '../includes/footer.php'; ?>
