<?php
require '../config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("Access denied");
}

$admin_id = $_SESSION['admin_id'];

/* =========================
   GET ADMIN OFFICE NAME ONLY
========================= */
$stmt = $conn->prepare("
    SELECT office_name 
    FROM admins 
    WHERE admin_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $admin_id);
$stmt->execute();

$admin_info = $stmt->get_result()->fetch_assoc();

$stmt->close();

$office_name = $admin_info['office_name'] ?? 'Unknown Office';

/* STUDENTS */
$students = $conn->query("SELECT * FROM students ORDER BY student_id DESC");

/* TOTAL */
$total_students = $conn->query("
    SELECT COUNT(*) as total FROM students
")->fetch_assoc()['total'] ?? 0;

/* ASSIGNED STEPS ONLY */
$step_stmt = $conn->prepare("
    SELECT * 
    FROM enrollment_steps 
    WHERE assigned_admin_id = ?
    ORDER BY step_order ASC
");

$step_stmt->bind_param("i", $admin_id);
$step_stmt->execute();
$admin_steps = $step_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background:#f4f6f9; }

        .header {
            background: linear-gradient(135deg,#1d3557,#457b9d);
            color:white;
            padding:20px;
            border-radius:12px;
        }

        .card-box {
            background:white;
            border-radius:12px;
            box-shadow:0 10px 25px rgba(0,0,0,0.08);
        }

        .step {
            border:1px solid #eee;
            border-radius:10px;
            padding:8px;
            margin-bottom:6px;
        }

        .collapse-btn {
            cursor:pointer;
            font-size:12px;
            color:#0d6efd;
        }

        .progress {
            height:6px;
        }

        /* =========================
           🔥 OFFICE BADGE DESIGN
        ========================= */

        .office-badge-wrapper{
            text-align: right;
        }

        .office-label{
            font-size: 11px;
            letter-spacing: 3px;
            opacity: 0.8;
            margin-bottom: 5px;
            color: #ffffffcc;
        }

        .office-badge{
            display: inline-block;
            padding: 14px 26px;
            font-size: 20px;
            font-weight: 800;
            border-radius: 14px;

            background: rgba(255,255,255,0.20);
            backdrop-filter: blur(12px);

            border: 1px solid rgba(255,255,255,0.35);

            box-shadow: 0 10px 25px rgba(0,0,0,0.25);

            text-transform: uppercase;
            letter-spacing: 1px;

            transition: 0.25s ease;

            min-width: 220px;
            text-align: center;
        }

        .office-badge:hover{
            transform: scale(1.06);
            background: rgba(255,255,255,0.28);
        }
    </style>
</head>

<body>

<div class="container py-4">

    <!-- HEADER -->
    <div class="header d-flex justify-content-between align-items-center mb-4">

        <div>
            <h3 class="mb-0">🎓 Admin Dashboard</h3>
            <small>Real-time Enrollment Tracker</small>
        </div>

        <!-- BEAUTIFUL OFFICE DISPLAY -->
        <div class="office-badge-wrapper mt-3">

    <div class="office-label text-center">
        CURRENT OFFICE
    </div>

    <div style="display:flex; justify-content:center;">
        <div class="office-badge">
            <?= htmlspecialchars($office_name) ?>
        </div>
    </div>

</div>

        <div>
            <a href="add_student.php" class="btn btn-success">➕ Add Student</a>
            <a href="../index.php" class="btn btn-primary ms-2">🏠 Home</a>
            <a href="qr_codes.php" class="btn btn-dark ms-2">📱 QR Codes</a>

            <span class="badge bg-light text-dark ms-2 p-2">
                Students: <?= $total_students ?>
            </span>
        </div>

    </div>

    <!-- QR SCANNER -->
    <div class="card-box p-3 mb-4 text-center">

        <h5>📷 QR Scanner</h5>

        <div class="row justify-content-center">
            <div class="col-md-6">

                <div id="reader" style="width:100%; min-height:300px;"></div>

                <p id="scan-result" class="mt-2 text-primary fw-bold"></p>

            </div>
        </div>

    </div>

    <!-- TABLE -->
    <div class="card-box p-3">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Program</th>
                    <th>Access Code</th>
                    <th>Progress</th>
                    <th>Steps</th>
                </tr>
            </thead>

            <tbody>

            <?php while($row = $students->fetch_assoc()): ?>

                <?php
                $student_id = $row['student_id'];

                $status_map = [];

                $stmt = $conn->prepare("
                    SELECT step_id, status 
                    FROM student_steps 
                    WHERE student_id = ?
                    ORDER BY updated_at DESC
                ");

                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $res = $stmt->get_result();

                while ($s = $res->fetch_assoc()) {
                    if (!isset($status_map[$s['step_id']])) {
                        $status_map[$s['step_id']] = $s['status'];
                    }
                }
                $stmt->close();

                $done = 0;
                $total = 0;

                $admin_steps->data_seek(0);

                while($step = $admin_steps->fetch_assoc()) {
                    $total++;

                    $status = $status_map[$step['step_id']] ?? 'pending';

                    if ($status === 'completed') {
                        $done++;
                    }
                }

                $progress = $total > 0 ? ($done / $total) * 100 : 0;
                ?>

                <tr>

                    <td class="fw-bold">
                        <?= htmlspecialchars($row['full_name']) ?>
                    </td>

                    <td>
                        <span class="badge bg-info text-dark">
                            <?= htmlspecialchars($row['program']) ?>
                        </span>
                    </td>

                    <td>
                        <code><?= htmlspecialchars($row['access_code']) ?></code>
                    </td>

                    <td style="min-width:140px;">
                        <div class="progress">
                            <div id="bar<?= $student_id ?>"
                                 class="progress-bar bg-success"
                                 style="width: <?= $progress ?>%">
                            </div>
                        </div>

                        <small id="text<?= $student_id ?>">
                            <?= round($progress) ?>%
                        </small>
                    </td>

                    <td>

                        <div class="collapse-btn mb-2"
                             onclick="toggleSteps('s<?= $student_id ?>')">
                            👁 Show / Hide Steps
                        </div>

                        <div id="s<?= $student_id ?>" style="display:none;">

                        <?php
                        $admin_steps->data_seek(0);
                        while($step = $admin_steps->fetch_assoc()):
                            $status = $status_map[$step['step_id']] ?? 'pending';
                        ?>

                            <div class="step">

                                <small class="fw-bold">
                                    <?= htmlspecialchars($step['step_name']) ?>
                                </small>

                                <select class="form-select form-select-sm mt-1"
                                    onchange="updateStatus(<?= $student_id ?>, <?= $step['step_id'] ?>, this.value)">

                                    <option value="pending" <?= $status=='pending'?'selected':'' ?>>⏳ Pending</option>
                                    <option value="completed" <?= $status=='completed'?'selected':'' ?>>✅ Completed</option>

                                </select>

                            </div>

                        <?php endwhile; ?>

                        </div>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- JS -->
<script>
function toggleSteps(id){
    let el = document.getElementById(id);
    el.style.display = (el.style.display === "none") ? "block" : "none";
}

function updateStatus(student_id, step_id, status){

   fetch("/enrollment_queue/admin/update_step.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: `student_id=${student_id}&step_id=${step_id}&status=${status}`
    })
    .then(() => {

        let selects = document.querySelectorAll(`#s${student_id} select`);

        let total = selects.length;
        let done = 0;

        selects.forEach(s => {
            if (s.value === "completed") done++;
        });

        let percent = total > 0 ? Math.round((done / total) * 100) : 0;

        document.getElementById("bar"+student_id).style.width = percent + "%";
        document.getElementById("text"+student_id).innerText = percent + "%";

    });

}
</script>

<!-- QR SCANNER -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
const scanner = new Html5Qrcode("reader");
let isScanning = false;

function onScanSuccess(decodedText) {

    if (isScanning) return;
    isScanning = true;

    document.getElementById("scan-result").innerText = "Scanning...";

    let code = decodedText.trim();

    try {
        let url = new URL(code);
        code = url.searchParams.get("access_code") || code;
    } catch (e) {}

    fetch("/enrollment_queue/scan_step.php", {
        method: "POST",
        headers: {"Content-Type":"application/x-www-form-urlencoded"},
        body: "access_code=" + encodeURIComponent(code)
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById("scan-result").innerText = data.message;

        if (data.status === "completed") {
            alert("Step completed!");
        }
    })
    .finally(() => {
        setTimeout(() => isScanning = false, 1500);
    });
}

Html5Qrcode.getCameras().then(cameras => {

    if (cameras.length) {

        scanner.start(
            cameras[0].id,
            { fps: 10, qrbox: 250 },
            onScanSuccess
        );

    } else {
        document.getElementById("scan-result").innerText = "No camera found";
    }

});
</script>

</body>
</html>