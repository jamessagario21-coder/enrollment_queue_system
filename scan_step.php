<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

file_put_contents("debug.txt", print_r($_POST, true));

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

/* STEP 1: GET ACCESS CODE */
$access_code = isset($_POST['access_code']) ? trim($_POST['access_code']) : '';

if ($access_code == '') {
    echo json_encode(["status"=>"error","message"=>"Invalid QR"]);
    exit;
}

/* FIND STUDENT */
$stmt = $conn->prepare("SELECT student_id FROM students WHERE access_code = ?");
$stmt->bind_param("s", $access_code);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(["status"=>"error","message"=>"Student not found"]);
    exit;
}

$student_id = $res->fetch_assoc()['student_id'];

/* =========================
   STEP 2: GET ADMIN STEPS
========================= */
$stmt = $conn->prepare("
    SELECT step_id, step_order, step_name
    FROM enrollment_steps
    WHERE assigned_admin_id = ?
    ORDER BY step_order ASC
");

$stmt->bind_param("i", $admin_id);
$stmt->execute();
$steps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* =========================
   STEP 3: CHECK FUNCTION
========================= */
function isCompleted($conn, $student_id, $step_id) {

    $q = $conn->prepare("
        SELECT status 
        FROM student_steps
        WHERE student_id=? AND step_id=?
    ");

    $q->bind_param("ii", $student_id, $step_id);
    $q->execute();

    $res = $q->get_result()->fetch_assoc();

    return $res && $res['status'] === 'completed';
}

/* =========================
   STEP 4: AUTO COMPLETE ALLOWED STEPS
========================= */
foreach ($steps as $s) {

    $step_name = strtoupper($s['step_name']);

    $allowed_auto = [
        'ENROLLMENT REQUIREMENTS',
        'REGISTRATION'
    ];

    if (in_array($step_name, $allowed_auto)) {

        if (!isCompleted($conn, $student_id, $s['step_id'])) {

            $q = $conn->prepare("
                SELECT status 
                FROM student_steps 
                WHERE student_id=? AND step_id=?
            ");

            $q->bind_param("ii", $student_id, $s['step_id']);
            $q->execute();
            $res = $q->get_result()->fetch_assoc();

            if (!$res || $res['status'] !== 'completed') {

                $ins = $conn->prepare("
                    INSERT INTO student_steps (student_id, step_id, status, updated_at)
                    VALUES (?, ?, 'completed', NOW())
                    ON DUPLICATE KEY UPDATE status='completed', updated_at=NOW()
                ");

                $ins->bind_param("ii", $student_id, $s['step_id']);
                $ins->execute();
            }
        }
    }
}

/* =========================
   STEP 5: FIND NEXT STEP
========================= */
$selected_step = null;

foreach ($steps as $step) {

    if (!isCompleted($conn, $student_id, $step['step_id'])) {
        $selected_step = $step;
        break;
    }
}

if (!$selected_step) {
    echo json_encode([
        "status"=>"done",
        "message"=>"All steps completed"
    ]);
    exit;
}

/* =========================
   STEP 6: COR RULE CHECK
========================= */
$cor_step = null;

foreach ($steps as $s) {
    if (stripos($s['step_name'], 'COR') !== false) {
        $cor_step = $s;
        break;
    }
}

if ($cor_step && $selected_step['step_id'] == $cor_step['step_id']) {

    foreach ($steps as $step) {
        if (!isCompleted($conn, $student_id, $step['step_id'])) {

            echo json_encode([
                "status" => "blocked",
                "message" => "Complete all steps before COR"
            ]);
            exit;
        }
    }
}

/* =========================
   STEP 7: COMPLETE STEP (SAFE FINAL FIX)
========================= */
$q = $conn->prepare("
    SELECT status 
    FROM student_steps 
    WHERE student_id=? AND step_id=?
");

$q->bind_param("ii", $student_id, $selected_step['step_id']);
$q->execute();
$res = $q->get_result()->fetch_assoc();

if (!$res || $res['status'] !== 'completed') {

    $stmt = $conn->prepare("
        INSERT INTO student_steps (student_id, step_id, status, updated_at)
        VALUES (?, ?, 'completed', NOW())
        ON DUPLICATE KEY UPDATE status=VALUES(status), updated_at=NOW()
    ");

    $stmt->bind_param("ii", $student_id, $selected_step['step_id']);
    $stmt->execute();
}

/* =========================
   RESPONSE
========================= */
echo json_encode([
    "status" => "completed",
    "message" => "Step updated",
    "step_id" => $selected_step['step_id'] ?? null
]);
exit;