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

// get all steps
$steps = [];
$steps_res = $conn->query("SELECT id, step_name, step_order FROM enrollment_steps WHERE is_active = 1 ORDER BY step_order ASC");
while ($row = $steps_res->fetch_assoc()) {
    $steps[] = $row;
}

// get students
$students_res = $conn->query("SELECT * FROM students ORDER BY queue_number ASC, created_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Enrollment Queue</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<h2>Enrollment Queue - Admin Dashboard</h2>
<p>Welcome, <?php echo $_SESSION['admin_username']; ?> | <a href="../logout.php">Logout</a></p>

<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Queue #</th>
        <th>Student No</th>
        <th>Name</th>
        <th>Program</th>
        <?php foreach ($steps as $step): ?>
            <th><?php echo htmlspecialchars($step['step_name']); ?></th>
        <?php endforeach; ?>
    </tr>
    <?php while ($stu = $students_res->fetch_assoc()): ?>
        <tr>
            <td><?php echo $stu['queue_number']; ?></td>
            <td><?php echo htmlspecialchars($stu['student_no']); ?></td>
            <td><?php echo htmlspecialchars($stu['full_name']); ?></td>
            <td><?php echo htmlspecialchars($stu['program']); ?></td>
            <?php
            // load this student's step statuses
            $status_map = [];
            $sid = $stu['id'];
            $ss_res = $conn->query("SELECT step_id, status FROM student_steps WHERE student_id = $sid");
            while ($row = $ss_res->fetch_assoc()) {
                $status_map[$row['step_id']] = $row['status'];
            }
            ?>
            <?php foreach ($steps as $step): 
                $step_id = $step['id'];
                $status = isset($status_map[$step_id]) ? $status_map[$step_id] : 'pending';
            ?>
                <td>
                    <form method="POST" action="update_step.php" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?php echo $sid; ?>">
                        <input type="hidden" name="step_id" value="<?php echo $step_id; ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?php if ($status=='pending') echo 'selected'; ?>>Pending</option>
                            <option value="completed" <?php if ($status=='completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </form>
                </td>
            <?php endforeach; ?>
        </tr>
    <?php endwhile; ?>
</table>
</body>
</html>
