<?php
ob_start();
echo "DEBUG: Logout started<br>";  // ← TEMP
session_start();
echo "DEBUG: Session destroyed<br>";  // ← TEMP
header('Location: ../index.php');
echo "DEBUG: Header sent<br>";  // ← WON'T SHOW
exit;
?>
