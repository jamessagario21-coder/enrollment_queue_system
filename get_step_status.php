<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'Not logged in']);
    exit;
}

$student_id = $_SESSION['student_id'];

// Example: get latest completed step
$stmt = $conn->prepare("SELECT step_name FROM enrollment_steps es
    JOIN enrollment_queue eq ON es.id = eq.step_id
    WHERE eq.student_id=? AND eq.completed=1
    ORDER BY eq.completed_at DESC LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(['status' => "Last completed step: " . $row['step_name']]);
} else {
    echo json_encode(['status' => "No steps completed yet"]);
}