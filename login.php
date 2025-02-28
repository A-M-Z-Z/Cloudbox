<?php
session_start(); // Start session to store user data

// Database Connection for LWS phpMyAdmin
$host = 'localhost';
$user = 'root';
$pass = 'root';
$dbname = 'cloudbox';
$conn = new mysqli($host, $user, $pass, $dbname);
$ip = $_SERVER['REMOTE_ADDR']; // Get user IP

if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);

// Handle Login with Session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $password = sha1($conn->real_escape_string($_POST['password']));
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['verified'] = $user['verified'];
        
       
            $verification_code = rand(100000, 999999);
            $query = "UPDATE users SET verification_code = '$verification_code' WHERE username = '{$_SESSION['username']}'";
                // Send Verification Email
                $subject = "Your CloudBOX 2FA Verification Code";
                $message = "Hello {$_SESSION['username']},\n\nYour 2FA verification code is: $verification_code\n\nThank you for registering with CloudBOX.";
                $headers = "From: no-reply@cloudbox.com";
                mail($_SESSION['email'], $subject, $message, $headers);
                
                header('Location: verify.php?username=' . urlencode($_SESSION['username']));
                exit;

        }
        }

        
     else {
        echo "<p style='color:red;'>Invalid username or password</p>";
 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudBOX - Login</title>
    <link rel="stylesheet" href="style-login.css">
</head>
<body>
    <div class="form-container">
        <?php if (!isset($_GET['register'])): ?>
            <h2>Login to CloudBOX</h2>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="?register=true">Sign Up</a></p>
        <?php else: ?>
            <h2>Create Your CloudBOX Account</h2>
            <form method="POST" action="register.php">
                <input type="text" name="full-name" placeholder="Full Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="date" name="birthdate" required>
                <input type="tel" name="phone" placeholder="Phone Number">
                <select name="country">
                    <option value="">Select your country</option>
                    <option value="USA">USA</option>
                    <option value="FR">France</option>
                    <option value="CA">Canada</option>
                    <option value="UK">UK</option>
                </select>
                <textarea name="bio" placeholder="Tell us about yourself"></textarea>
                <button type="submit">Create Account</button>
            </form>
            <p>Already have an account? <a href="index.html">Login</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
