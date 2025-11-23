<?php
$id = $_GET['id'];
$author = $_GET['author'];
$message = $_GET['message'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="styles2.css">
</head>
<body class="comment">
    <form action="update.php"  method="get" id="form">
        <label for="author">author:</label>
        <input type="text" id="author" name="author" placeholder="author" value="<?php echo $author; ?>" >
        <br>
        <label for="message">message:</label>
        <textarea type="text" id="message" name="message" id="commentText" placeholder="Add your comment here..."  ><?php echo $message; ?></textarea>
        <br>
        <label for="currentTime">Current Time:</label>
        <input type="text" id="currentTime" name="currentTime" readonly>
        
        <input type="text" id="id" name="id" value="<?php echo $id; ?>" readonly hidden> 
        
        <input type="submit" value="Modify" class="comment">
        <script src="script.js"></script>
</body>
</html>
