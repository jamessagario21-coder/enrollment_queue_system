<?php
// =============================
// ENROLLMENT STEP TRACKER UI
// Admin + Student View System
// =============================

require '../config.php';
session_start();

// CHANGE THIS if needed
$student_id = $_GET['student_id'] ?? null;

// =============================
// GET ALL STEPS
// =============================
$steps = $conn->query("SELECT * FROM enrollment_steps WHERE is_active=1 ORDER BY step_order ASC");

// =============================
// GET STUDENT DATA
// =============================
$student = null;
$status_map = [];

if ($student_id) {
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT step_id, status FROM student_steps WHERE student_id=?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $status_map[$row['step_id']] = $row['status'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">

    <h2 class="mb-4">🎓 Enrollment Step Tracker</h2>

    <?php if ($student): ?>
        <div class="card mb-4 p-3">
            <h4><?= htmlspecialchars($student['full_name']) ?></h4>
            <p class="text-muted">Program: <?= htmlspecialchars($student['program']) ?></p>
            <p><b>Access Code:</b> <?= htmlspecialchars($student['access_code']) ?></p>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <?php while($step = $steps->fetch_assoc()):
                $sid = $step['step_id'];
                $status = $status_map[$sid] ?? 'pending';
            ?>

            <div class="border rounded p-3 mb-3 d-flex justify-content-between align-items-center">

                <!-- STEP INFO -->
                <div>
                    <h5 class="mb-1">
                        Step <?= $step['step_order'] ?>: <?= htmlspecialchars($step['step_name']) ?>
                    </h5>
                    <small class="text-muted">
                        <?= htmlspecialchars($step['description']) ?>
                    </small>
                </div>

                <!-- STATUS BADGE -->
                <div class="text-end">
                    <span class="badge bg-<?= $status=='completed'?'success':'warning' ?> p-2">
                        <?= strtoupper($status) ?>
                    </span>
                </div>

            </div>

            <?php endwhile; ?>

        </div>
    </div>

</div>

</body>
</html>


<!-- ============================= -->
<!-- ADMIN INLINE CONTROL SYSTEM -->
<!-- ============================= -->

<?php if (isset($_SESSION['admin_id'])): ?>

<div class="container mt-5">
    <h3>🛠 Admin Step Control</h3>

    <?php
    $students = $conn->query("SELECT * FROM students ORDER BY student_id DESC");
    $steps = $conn->query("SELECT * FROM enrollment_steps ORDER BY step_order ASC");
    ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student</th>
                <?php foreach($steps as $s): ?>
                    <th><?= $s['step_name'] ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>

        <tbody>
        <?php foreach($students as $stud): ?>
            <tr>

                <?php
                $status_map = [];
                $stmt = $conn->prepare("SELECT step_id, status FROM student_steps WHERE student_id=?");
                $stmt->bind_param("i", $stud['student_id']);
                $stmt->execute();
                $res = $stmt->get_result();

                while ($r = $res->fetch_assoc()) {
                    $status_map[$r['step_id']] = $r['status'];
                }
                ?>

                <?php $admin_step = $_SESSION['admin_step_id'];
                        $steps = $conn->query("
                            SELECT * FROM enrollment_steps 
                            WHERE step_id = $admin_step
                        ");
                ?>

                <td>
                    <form method="POST" action="update_step.php">
                        <input type="hidden" name="student_id" value="<?= $stud['student_id'] ?>">
                        <input type="hidden" name="step_id" value="<?= $sid ?>">

                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="pending" <?= $status=='pending'?'selected':'' ?>>Pending</option>
                            <option value="completed" <?= $status=='completed'?'selected':'' ?>>Completed</option>
                        </select>
                    </form>
                </td>

                <?php endforeach; ?>

            </tr>
        </tbody>

    </table>
</div>

<?php endif; ?>
