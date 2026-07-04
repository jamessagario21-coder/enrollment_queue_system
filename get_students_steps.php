<?php
require 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['student_id'])) {
    echo json_encode([]);
    exit;
}

$student_id = (int) $_GET['student_id'];

$stmt = $conn->prepare("
    SELECT step_id, status
    FROM student_steps
    WHERE student_id = ?
");

$stmt->bind_param("i", $student_id);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);