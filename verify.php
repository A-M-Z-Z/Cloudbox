<?php
session_start(); // Start session at the very beginning

// Database Connection
$host = '91.216.107.164';
$user = 'amzz2427862';
$pass = '37qB5xqen4prX8@';
$dbname = 'amzz2427862';
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle 2FA Verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['verification_code']);
    $username = isset($_GET['username']) ? $conn->real_escape_string($_GET['username']) : '';

    // Fetch user data
    $query = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email'];

        // Verify the code
        if ($user['verification_code'] == $code) {
            $update = "UPDATE users SET verification_code=NULL, verified=1 WHERE username='$username'";
            if ($conn->query($update)) { // Fixed: Added missing closing parenthesis
                header('Location: /home');
                exit;
            } else {
                echo "<p style='color:red;'>Failed to update verification status.</p>";
            }
        } else {
            echo "<p style='color:red;'>Invalid verification code</p>";
        }
    } else {
        echo "<p style='color:red;'>User not found.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudBOX - Verify 2FA</title>
    <link rel="stylesheet" href="/style-login.css">
</head>
<body>
    <div class="form-container">
        <h2>Verify Your Email</h2>
        <p>We sent a 6-digit code to your email. Enter it below:</p>
        <form method="POST">
            <input type="text" name="verification_code" placeholder="Enter verification code" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>