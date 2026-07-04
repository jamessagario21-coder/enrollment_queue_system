<?php
require '../config.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    exit("Access denied");
}

/* GET ALL STEPS */
$steps = $conn->query("
    SELECT * FROM enrollment_steps 
    ORDER BY step_order ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>QR Code Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container py-4">

    <h3>📱 QR Code Hub</h3>
    <p>All step QR codes for enrollment system</p>

    <a href="dashboard.php" class="btn btn-secondary mb-3">⬅ Back</a>

    <div class="row">

    <?php while($step = $steps->fetch_assoc()): ?>

        <div class="col-md-4 mb-3">

            <div class="card shadow-sm p-3 text-center">

                <h5><?= htmlspecialchars($step['step_name']) ?></h5>

                <p class="text-muted">
                    📍 <?= htmlspecialchars($step['location'] ?? 'No location') ?>
                </p>

                <?php
                /* FIX: QR NOW REPRESENTS STEP ONLY */
                $qr_data = $step['step_id'];
                ?>

                <img 
                    src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($qr_data) ?>"
                >

                <small class="text-muted d-block mt-2">
                    Scan this to complete step <?= htmlspecialchars($step['step_order']) ?>
                </small>

            </div>

        </div>

    <?php endwhile; ?>

    </div>

</div>

</body>
</html>