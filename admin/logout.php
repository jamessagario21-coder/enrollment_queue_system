<?php
ob_start();
session_start();
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"]);
}
session_destroy();
header('Location: index.php');
exit;
?>
