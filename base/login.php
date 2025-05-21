<?php include '../includes/header.php'; ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $password = sanitize($_POST['password']);
    $p_errors = [];
    $e_errors = [];

    if ($email == '' || $password == '') {
        $errors[] = 'Please fill all the fields.';
    } else {
        $check = $conn->prepare("SELECT id, email, name, password, role FROM users WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                switch ($user['role']) {
                    case 1:
                        $_SESSION['role'] = 'admin';
                        break;
                    case 2:
                        $_SESSION['role'] = 'agent';
                        break;
                    case 3:
                        $_SESSION['role'] = 'client';
                        break;
                }
 

                if($user['role'] == 'client') {
                    header("Location: ../client/dashboard.php");
                } elseif ($user['role'] == 'agent') {
                    header("Location: ../agent/dashboard.php");
                } elseif ($user['role'] == 'admin') {
                    header("Location: ../admin/dashboard.php");
                }
                exit();
            } else {
                $p_errors[] = "Invalid password";
            }
        } else {
            $e_errors[] = "No account found with this email";
        }
    }
}
?>
<style>
    #container{
        margin:7% auto;
    }
</style>
    <h2 style="text-align:center;">Login here</h2>
<form action="login.php" method="POST">
    <input type="text" name="email" placeholder="Enter your Email" required>
    <?php
        if (!empty($e_errors)) {
        foreach ($e_errors as $e_error) {
            echo "<small style='color:red;'>$e_error</small>";
        }
    }
    ?>
    <input type="password" name="password" placeholder="Enter your Password" required>
    <?php
        if (!empty($p_errors)) {
        foreach ($p_errors as $p_error) {
            echo "<small style='color:red;'>$p_error</small>";
        }
    }
    ?>
    <button type="submit">Login</button>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
    <p>Your Password <a href="forgot-password.php"> Forgot Here</a></p>
</form>

<?php include '../includes/footer.php'; ?>
