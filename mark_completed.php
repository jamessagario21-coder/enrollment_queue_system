<?php
require 'config.php'; // your database connection

// Get URL parameters
$step_id = isset($_GET['step_id']) ? intval($_GET['step_id']) : 0;
$access_code = isset($_GET['access_code']) ? $_GET['access_code'] : '';

// Validate inputs
if (!$step_id || !$access_code) {
    die("Invalid QR code.");
}

// Optional: verify access code
$stmt = $conn->prepare("SELECT * FROM steps WHERE id=? AND access_code=?");
$stmt->bind_param("is", $step_id, $access_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Unauthorized scan.");
}

// Mark step as completed
$update = $conn->prepare("UPDATE steps SET completed=1 WHERE id=?");
$update->bind_param("i", $step_id);
$update->execute();

// Show confirmation
echo "<h2>Step marked as completed! ✅</h2>";
?>