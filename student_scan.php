<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

/* MUST HAVE LOGIN SESSION */
if (!isset($_SESSION['access_code'])) {
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$step_id = $_POST['step_id'] ?? 0;

if ($step_id == 0) {
    echo json_encode(["status"=>"error","message"=>"Invalid step"]);
    exit;
}

/* GET STUDENT */
$stmt = $conn->prepare("
    SELECT student_id 
    FROM students 
    WHERE access_code = ?
");
$stmt->bind_param("s", $_SESSION['access_code']);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status"=>"error","message"=>"Student not found"]);
    exit;
}

$student_id = $res->fetch_assoc()['student_id'];

/* MARK STEP COMPLETE */
$stmt = $conn->prepare("
    INSERT INTO student_steps (student_id, step_id, status, updated_at)
    VALUES (?, ?, 'completed', NOW())
    ON DUPLICATE KEY UPDATE 
        status = 'completed',
        updated_at = NOW()
");

$stmt->bind_param("ii", $student_id, $step_id);
$stmt->execute();

echo json_encode([
    "status" => "completed",
    "message" => "Step marked completed"
]);