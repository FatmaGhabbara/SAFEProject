<?php
// Redirect root requests to the login page when serving SAFEProject directly
header('Location: /view/frontoffice/login.php');
exit();
?>
