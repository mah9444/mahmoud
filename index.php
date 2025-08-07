<?php
// Ø¥Ø¹Ø¯Ø§Ø¯Ø§Øª Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "file_uploader";

// Ø¥Ù†Ø´Ø§Ø¡ Ø§Ù„Ø§ØªØµØ§Ù„
$conn = new mysqli($servername, $username, $password, $dbname);

// ÙØ­Øµ Ø§Ù„Ø§ØªØµØ§Ù„
if ($conn->connect_error) {
    die("ÙØ´Ù„ Ø§Ù„Ø§ØªØµØ§Ù„: " . $conn->connect_error);
}

// Ù…Ø¹Ø§Ù„Ø¬Ø© Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $target_dir = "uploads/";
    
    // Ø¥Ù†Ø´Ø§Ø¡ Ù…Ø¬Ù„Ø¯ uploads Ø¥Ø°Ø§ Ù„Ù… ÙŠÙƒÙ† Ù…ÙˆØ¬ÙˆØ¯Ø§Ù‹
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_name = basename($_FILES["file"]["name"]);
    $target_file = $target_dir . $file_name;
    $uploadOk = 1;
    
    // ÙØ­Øµ Ø­Ø¬Ù… Ø§Ù„Ù…Ù„Ù
    if ($_FILES["file"]["size"] > 50000000) { // 50MB
        echo "<div class='error'>Ø¹Ø°Ø±Ø§Ù‹ØŒ Ø§Ù„Ù…Ù„Ù ÙƒØ¨ÙŠØ± Ø¬Ø¯Ø§Ù‹.</div>";
        $uploadOk = 0;
    }
    
    // ÙØ­Øµ Ø¥Ø°Ø§ ÙƒØ§Ù† $uploadOk = 0 Ø¨Ø³Ø¨Ø¨ Ø®Ø·Ø£
    if ($uploadOk == 0) {
        echo "<div class='error'>Ø¹Ø°Ø±Ø§Ù‹ØŒ Ù„Ù… ÙŠØªÙ… Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù.</div>";
    } else {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            // Ø­ÙØ¸ Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„Ù…Ù„Ù ÙÙŠ Ù‚Ø§Ø¹Ø¯Ø© Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª
            $sql = "INSERT INTO files (file_name, file_path) VALUES ('$file_name', '$target_file')";
            
            if ($conn->query($sql) === TRUE) {
                echo "<div class='success'>ØªÙ… Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù Ø¨Ù†Ø¬Ø§Ø­: " . htmlspecialchars($file_name) . "</div>";
            } else {
                echo "<div class='error'>Ø®Ø·Ø£: " . $sql . "<br>" . $conn->error . "</div>";
            }
        } else {
            echo "<div class='error'>Ø¹Ø°Ø±Ø§Ù‹ØŒ Ø­Ø¯Ø« Ø®Ø·Ø£ Ø£Ø«Ù†Ø§Ø¡ Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù.</div>";
        }
    }
}

// Ø¬Ù„Ø¨ Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ù„ÙØ§Øª Ø§Ù„Ù…Ø±ÙÙˆØ¹Ø©
$sql = "SELECT * FROM files ORDER BY upload_time DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ø±ÙØ¹ Ø§Ù„Ù…Ù„ÙØ§Øª</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Ù†Ø¸Ø§Ù… Ø±ÙØ¹ Ø§Ù„Ù…Ù„ÙØ§Øª</h1>
        
        <div class="upload-section">
            <h2>Ø±ÙØ¹ Ù…Ù„Ù Ø¬Ø¯ÙŠØ¯</h2>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="file-input-wrapper">
                    <input type="file" name="file" id="file" required>
                    <label for="file">Ø§Ø®ØªØ± Ù…Ù„Ù</label>
                </div>
                <button type="submit" class="upload-btn">Ø±ÙØ¹ Ø§Ù„Ù…Ù„Ù</button>
            </form>
        </div>
        
        <div class="files-section">
            <h2>Ø§Ù„Ù…Ù„ÙØ§Øª Ø§Ù„Ù…Ø±ÙÙˆØ¹Ø©</h2>
            <?php if ($result->num_rows > 0): ?>
                <div class="files-grid">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="file-card">
                            <div class="file-icon">ðŸ“„</div>
                            <div class="file-info">
                                <h3><?php echo htmlspecialchars($row["file_name"]); ?></h3>
                                <p>ØªØ§Ø±ÙŠØ® Ø§Ù„Ø±ÙØ¹: <?php echo $row["upload_time"]; ?></p>
                                <a href="<?php echo $row["file_path"]; ?>" download class="download-btn">ØªØ­Ù…ÙŠÙ„</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="no-files">Ù„Ø§ ØªÙˆØ¬Ø¯ Ù…Ù„ÙØ§Øª Ù…Ø±ÙÙˆØ¹Ø© Ø¨Ø¹Ø¯.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>

