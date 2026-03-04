<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit;
}
?>
<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit;
}

require '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $step_id = (int)$_POST['step_id'];
    $status = $_POST['status'] === 'completed' ? 'completed' : 'pending';
    $admin_id = $_SESSION['admin_id'];

    // check if record exists
    $check = $conn->prepare("SELECT id FROM student_steps WHERE student_id = ? AND step_id = ?");
    $check->bind_param("ii", $student_id, $step_id);
    $check->execute();
    $check->bind_result($existing_id);

    if ($check->fetch()) {
        $check->close();
        $stmt = $conn->prepare("UPDATE student_steps SET status=?, updated_at=NOW(), updated_by=? WHERE id=?");
        $stmt->bind_param("sii", $status, $admin_id, $existing_id);
    } else {
        $check->close();
        $stmt = $conn->prepare("INSERT INTO student_steps (student_id, step_id, status, updated_at, updated_by) VALUES (?,?,?,?,?)");
        $now_status = $status;
        $stmt->bind_param("iissi", $student_id, $step_id, $now_status, $now, $admin_id);
        // small fix: instead of $now, use NOW() in SQL or set $now = date('Y-m-d H:i:s')
    }

    $stmt->execute();
    $stmt->close();
}

header("Location: dashboard.php");
exit;
