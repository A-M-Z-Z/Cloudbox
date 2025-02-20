<?php
session_start();
// CloudBOX User Registration with Email 2FA (register.php)
$host = '91.216.107.164';
$user = 'amzz2427862';
$pass = '37qB5xqen4prX8@';
$dbname = 'amzz2427862';
$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle Registration with Email 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $conn->real_escape_string($_POST['full-name']);
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = sha1($conn->real_escape_string($_POST['password']));
    $birthdate = $conn->real_escape_string($_POST['birthdate']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $country = $conn->real_escape_string($_POST['country']);
    $bio = $conn->real_escape_string($_POST['bio']);
    
    // Generate 2FA Code
    $verification_code = rand(100000, 999999);
    
    // Insert User Data with 2FA Code
    $query = "INSERT INTO users (full_name, email, username, password, birthdate, phone, country, bio, verification_code,verified) 
              VALUES ('$full_name', '$email', '$username', '$password', '$birthdate', '$phone', '$country', '$bio', '$verification_code',0)";
    
    if ($conn->query($query) === TRUE) {
    
        
        $subject = "Your CloudBOX account was susccesfully created";
        $message = "Hello $full_name,\n\nYou have created account successfully.\n\n Your username is $username ";
        $headers = "From: no-reply@cloudbox.com";
        mail($email, $subject, $message, $headers);
        
        // Redirect to 2FA Verification Page
        
header("Location: redirect");
exit;
  }
    else {
        echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
    }
    
}
?>
