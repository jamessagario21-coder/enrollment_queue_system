<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit;
}
?>
