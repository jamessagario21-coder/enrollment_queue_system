<?php
require 'config.php';

$student = null;
$steps = [];
$status_map = [];
$error = "";

if (isset($_GET['student_no'])) {
    $student_no = $_GET['student_no'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE student_no = ?");
    $stmt->bind_param("s", $student_no);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();

        // load steps
        $steps_res = $conn->query("SELECT id, step_name, step_order FROM enrollment_steps WHERE is_active = 1 ORDER BY step_order ASC");
        while ($row = $steps_res->fetch_assoc()) {
            $steps[] = $row;
        }

        // load student step statuses
        $sid = $student['id'];
        $ss_res = $conn->query("SELECT step_id, status FROM student_steps WHERE student_id = $sid");
        while ($row = $ss_res->fetch_assoc()) {
            $status_map[$row['step_id']] = $row['status'];
        }
    } else {
        $error = "Student not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Status</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<h2>Enrollment Status</h2>

<form method="GET" action="student_view.php">
    <label>Student Number:</label>
    <input type="text" name="student_no" required>
    <button type="submit">View Status</button>
</form>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($student): ?>
    <h3><?php echo htmlspecialchars($student['full_name']); ?> (<?php echo htmlspecialchars($student['student_no']); ?>)</h3>
    <p>Program: <?php echo htmlspecialchars($student['program']); ?></p>
    <p>Queue #: <?php echo $student['queue_number']; ?></p>

    <ul class="checklist">
        <?php foreach ($steps as $step): 
            $step_id = $step['id'];
            $status = isset($status_map[$step_id]) ? $status_map[$step_id] : 'pending';
            $class = $status === 'completed' ? 'completed' : 'pending';
        ?>
            <li class="<?php echo $class; ?>">
                <?php if ($status === 'completed'): ?>
                    ✅
                <?php else: ?>
                    ⏳
                <?php endif; ?>
                <?php echo htmlspecialchars($step['step_name']); ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

</body>
</html>
