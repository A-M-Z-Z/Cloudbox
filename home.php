<?php
session_start(); // Start session

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    header("Location: expired");  // Redirige vers la page de connexion si non connecté
    exit();
}

// Increase limits for large file operations
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 600);
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', 'Off');

// Database Connection
$host = '91.216.107.164';
$user = 'amzz2427862';
$pass = '37qB5xqen4prX8@';
$dbname = 'amzz2427862';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Database connection failed: " . $conn->connect_error);
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 60);

$username = $_SESSION['username'];
$userid = $_SESSION['user_id'];

// Handle File Upload with Duplicate Check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $filename = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_size = $_FILES['file']['size'];
    $file_type = $_FILES['file']['type'];

    // Check if file already exists for the user
    $checkStmt = $conn->prepare("SELECT id FROM files WHERE user_id = ? AND filename = ?");
    $checkStmt->bind_param("is", $_SESSION['user_id'], $filename);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "<p id='upload-message' style='color:red;'>File already exists for this user.</p>";
    } else {
        // Read the file content
        $file_content = file_get_contents($file_tmp);

        // Insert file metadata into the `files` table
        $insertStmt = $conn->prepare("INSERT INTO files (user_id, filename, file_size, file_type) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("isis", $_SESSION['user_id'], $filename, $file_size, $file_type);
        if ($insertStmt->execute()) {
            $file_id = $insertStmt->insert_id;

            // Insert file content into the `file_content` table
            $contentStmt = $conn->prepare("INSERT INTO file_content (file_id, content) VALUES (?, ?)");
            $contentStmt->bind_param("is", $file_id, $file_content);
            if ($contentStmt->execute()) {
                echo "<p id='upload-message' style='color:green;'>File uploaded successfully.</p>";
            } else {
                echo "<p id='upload-message' style='color:red;'>Error saving file content.</p>";
            }
        } else {
            echo "<p id='upload-message' style='color:red;'>Error saving file metadata.</p>";
        }
    }
}

// Handle File Download
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $file_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT f.filename, f.file_type, fc.content FROM file_content fc JOIN files f ON fc.file_id = f.id WHERE f.id = ? AND f.user_id = ?");
    $stmt->bind_param("ii", $file_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($filename, $filetype, $filecontent);

    if ($stmt->fetch()) {
        header("Content-Type: $filetype");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Content-Length: " . strlen($filecontent));
        if (ob_get_level()) ob_end_clean();
        echo $filecontent;
        exit;
    } else {
        echo "<p id='download-message' style='color:red;'>File not found.</p>";
    }
}

// Handle File Deletion
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $deleteStmt = $conn->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
    $deleteStmt->bind_param("ii", $delete_id, $_SESSION['user_id']);
    if ($deleteStmt->execute()) {
        echo "<p id='delete-message' style='color:green;'>File deleted successfully.</p>";
    } else {
        echo "<p id='delete-message' style='color:red;'>Error deleting file.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CloudBOX - Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">
            <img src="logo.png" alt="CloudBOX Logo" height="40">
        </div>
        <h1>CloudBOX</h1>
        <div class="search-bar">
            <input type="text" placeholder="Search here...">
        </div>
    </div>
    
    <nav class="dashboard-nav">
        <a href="#">ðÂÂÂÂÂÂ Dashboard</a>
        <a href="drive">ðÂÂÂÂÂÂ My Drive</a>
        <a href="#">ðÂÂÂÂÂÂ Files</a>
        <a href="#">ðÂÂÂÂÂÂ Recent</a>
        <a href="#">â­ÂÂ Favourites</a>
        <a href="#">ðÂÂÂÂÂÂï¸ÂÂ Trash</a>
        <a href="logout.php">ðÂÂÂÂª Logout</a>
    </nav>

    <main>
        <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>
   
        <h2>My Drive - Upload Files</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit">Upload</button>
        </form>

        <section>
            <h3>Uploaded Files</h3>
            <ul>
                <?php
                $result = $conn->query("SELECT * FROM files WHERE user_id = $userid");
                while ($file = $result->fetch_assoc()) {
                    echo "<li>{$file['filename']} ({$file['file_size']} bytes) - 
                          <a href='download.php?id={$file['id']}'>Download</a> | 
                          <a href='?delete_id={$file['id']}' onclick='return confirm(\"Are you sure you want to delete this file?\");'>Delete</a></li>";
                }
                ?>
            </ul>
        </section>
    </main>

    <!-- JavaScript to hide messages after 3 seconds -->
    <script>
    function hideMessage(elementId, delay) {
        setTimeout(function() {
            var messageElement = document.getElementById(elementId);
            if (messageElement) {
                messageElement.style.display = 'none';
            }
        }, delay);
    }

    // Hide upload message after 3 seconds
    hideMessage('upload-message', 3000);

    // Hide delete message after 3 seconds
    hideMessage('delete-message', 3000);

    // Hide download message after 3 seconds
    hideMessage('download-message', 3000);
    </script>
</body>
</html>