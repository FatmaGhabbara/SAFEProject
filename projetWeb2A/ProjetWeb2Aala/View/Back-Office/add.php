<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="styles2.css">
</head>
<body class="comment">
    <form action="Ajout.php"  method="get" id="form">
        <label for="author">author:</label>
        <input type="text" id="author" name="author" placeholder="author">
        <br>
        <label for="message">message:</label>
        <textarea type="text" id="message" name="message" id="commentText" placeholder="Add your comment here..." ></textarea>
        <br>
         <label for="currentTime">Current Time:</label>
        <input type="text" id="currentTime" name="currentTime" readonly>
        <br>
        <input type="submit" value="add" class="comment">
        <script src="script.js"></script>
</body>
</html>