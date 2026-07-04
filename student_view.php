<?php
session_start();
require 'config.php';

$student = null;
$steps = [];
$status_map = [];
$error = "";

if (isset($_GET['access_code'])) {

    $access_code = $_GET['access_code'];

    $stmt = $conn->prepare("
        SELECT student_id, full_name, program, access_code 
        FROM students 
        WHERE access_code = ?
    ");
    $stmt->bind_param("s", $access_code);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $student = $result->fetch_assoc();
        $_SESSION['access_code'] = $student['access_code'];
        $student_id = $student['student_id'];

        $steps_res = $conn->query("
            SELECT * 
            FROM enrollment_steps 
            WHERE is_active = 1 
            ORDER BY step_order ASC
        ");

        while ($row = $steps_res->fetch_assoc()) {
            $steps[] = $row;
        }

        $stmt2 = $conn->prepare("
            SELECT step_id, status 
            FROM student_steps 
            WHERE student_id = ?
        ");

        $stmt2->bind_param("i", $student_id);
        $stmt2->execute();

        $res2 = $stmt2->get_result();

        while ($row = $res2->fetch_assoc()) {
            $status_map[$row['step_id']] = $row['status'];
        }

        $stmt2->close();

    } else {
        $error = "Student not found.";
    }

    $stmt->close();

} else {
    $error = "Missing access code.";
}
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student View</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/html5-qrcode"></script>

<style>
body{
    background:#eef2f7;
}

.container{
    max-width: 900px;
}

/* CARDS */
.card{
    border: none;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

/* STEPS */
.step-card{
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
}

.step-card.completed{
    border-left: 6px solid #28a745;
    background: #f0fff4;
}

.step-card.pending{
    border-left: 6px solid #ffc107;
    background: #fffbea;
}

/* QR FIX (SMALLER FOR PC) */
.qr-image{
    width: 110px;
    height: 110px;
    display: block;
    margin: 0 auto;
}

/* SCANNER FIX */
#qr-reader{
    width: 100%;
    max-width: 380px;
    margin: auto;
    border-radius: 12px;
    overflow: hidden;
}
</style>
</head>

<body class="p-3">

<?php if ($student): ?>

<div class="container">

    <!-- HEADER -->
    <div class="text-center mb-3">
        <h3 class="fw-bold">Welcome, <?= htmlspecialchars($student['full_name']); ?></h3>
        <small class="text-muted"><?= htmlspecialchars($student['program']); ?></small>
    </div>

    <!-- QR CARD -->
    <div class="card p-3 mb-3 text-center">

        <h5 class="mb-2">🎫 Your Student QR</h5>

        <?php $qr_data = $student['access_code']; ?>

        <img 
            class="qr-image"
            src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=<?= urlencode($qr_data) ?>" 
        />

        <div class="mt-2">
            <code><?= htmlspecialchars($qr_data) ?></code>
        </div>

    </div>

    <!-- SCANNER -->
    <div class="card p-3 mb-3 text-center">

        <h5 class="mb-3">📷 Scan Step QR</h5>

        <select id="camera-select" class="form-select mb-3"></select>

        <div id="qr-reader"></div>

        <p id="qr-result" class="mt-3 fw-bold text-primary"></p>

    </div>

    <!-- STEPS -->
    <div class="card p-3">

        <h5 class="mb-3">📋 Enrollment Steps</h5>

        <?php foreach ($steps as $step):

            $step_id = $step['step_id'];
            $status = $status_map[$step_id] ?? 'pending';
            $done = $status === 'completed';

        ?>

        <div class="step-card <?= $done ? 'completed' : 'pending'; ?>">

            <div class="fw-bold">
                <?= htmlspecialchars($step['step_name']); ?>
            </div>

            <small class="text-muted">
                📍 <?= htmlspecialchars($step['location'] ?? 'No location assigned'); ?>
            </small>

            <div class="mt-1">
                <span class="badge <?= $done ? 'bg-success' : 'bg-warning text-dark'; ?>">
                    <?= $done ? 'Completed' : 'Pending'; ?>
                </span>
            </div>

        </div>

        <?php endforeach; ?>

    </div>

</div>

<script>
const qrReader = new Html5Qrcode("qr-reader");

function startScanner(cameraId) {
    qrReader.start(
        cameraId,
        { fps: 10, qrbox: 250 },
        onScanSuccess
    );
}

Html5Qrcode.getCameras().then(cameras => {

    const select = document.getElementById("camera-select");

    cameras.forEach(cam => {
        let opt = document.createElement("option");
        opt.value = cam.id;
        opt.text = cam.label || "Camera";
        select.appendChild(opt);
    });

    startScanner(cameras[0].id);

    select.onchange = () => {
        qrReader.stop().then(() => {
            startScanner(select.value);
        });
    };
});

function onScanSuccess(decodedText) {

    let step_id = decodedText.trim();

    document.getElementById("qr-result").innerText = "Processing...";

    fetch("student_scan.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "step_id=" + encodeURIComponent(step_id)
    })
    .then(res => res.json())
    .then(data => {

        document.getElementById("qr-result").innerText = data.message;

        if (data.status === "completed") {
            location.reload();
        }

    });
}
</script>

<?php else: ?>

<div class="container">
    <div class="alert alert-danger text-center">
        <?= $error ?>
    </div>
</div>

<?php endif; ?>

</body>
</html>