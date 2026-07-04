<?php
session_start();
require '../config.php';

if (!isset($_SESSION['admin_id'])) {
    exit('Access denied');
}

$student_id = (int)($_POST['student_id'] ?? 0);
$step_id = (int)($_POST['step_id'] ?? 0);
$status = $_POST['status'] ?? 'pending';

if ($student_id <= 0 || $step_id <= 0) {
    exit('Invalid request');
}

$status = ($status === 'completed') ? 'completed' : 'pending';

/* FORCE SINGLE SOURCE OF TRUTH */
$stmt = $conn->prepare("
    INSERT INTO student_steps (student_id, step_id, status, updated_at)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        updated_at = NOW()
");

$stmt->bind_param("iis", $student_id, $step_id, $status);
$stmt->execute();

echo "OK";
?>