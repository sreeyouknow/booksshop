<?php include '../includes/header.php'; ?>
<?php
// Load Composer's autoloader
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send reset email
function send_reset_email($email, $token) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sreeranganathan03@gmail.com';  // Your Gmail
        $mail->Password   = 'dwvg qoje lsww dpdq';         // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('sreeranganathan03@gmail.com', 'Book Can');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset Request';
        $reset_link = "http://localhost/book-can/base/reset-password.php?token=$token";
        $mail->Body    = "Click <a href='$reset_link'>here</a> to reset your password.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitize($_POST['email']);
    $errors = [];

    if (empty($email)) {
        $errors[] = 'Email is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $token = bin2hex(random_bytes(50));
            $expiry_time = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?");
            $update->bind_param('sss', $token, $expiry_time, $email);
            $update->execute();

            if (send_reset_email($email, $token)) {
                echo "<p style='color:green;'>Reset link sent to your email.</p>";
            } else {
                echo "<p style='color:red;'>Failed to send reset email.</p>";
            }
        } else {
            $errors[] = "No account found with that email.";
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>
<section style="height:75vh; align-items:center;"><br><br><br>
<h2 style="text-align:center;">Forgot Password here</h2>
<form action="forgot-password.php" method="POST">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
</form>
</section>
<?php include '../includes/footer.php'; ?>
