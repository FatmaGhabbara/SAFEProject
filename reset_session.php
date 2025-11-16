<?php
session_start();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');
session_regenerate_id(true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Session Reset</title>
</head>
<body>
    <h1>✅ Session détruite !</h1>
    <p>Cliquez sur un lien ci-dessous :</p>
    <ul>
        <li><a href="view/frontoffice/support/support_info.php">Frontend (User)</a></li>
        <li><a href="view/backoffice/support/support_requests.php">Backend (Admin)</a></li>
        <li><a href="view/backoffice/support/counselors_list.php">Liste des Conseillers (Admin)</a></li>
    </ul>
</body>
</html>

