<?php
require '../config.php';
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Steps (QR Codes)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4 bg-light">

<h2 class="mb-4">📌 Step QR Codes</h2>

<?php
$steps = $conn->query("SELECT * FROM enrollment_steps WHERE is_active=1 ORDER BY step_order");

// Get current host dynamically
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];  // e.g., localhost or ngrok URL
$base_url = $protocol . "://" . $host . "/enrollment_queue/scan_step.php";

while($step = $steps->fetch_assoc()):
    $step_id = $step['step_id'];
    $step_name = $step['step_name'];

    // QR link uses dynamic host
    $qr_link = "https://elvin-unshepherded-pinnatedly.ngrok-free.dev/enrollment_queue/scan_step.php?step_id=$step_id";
?>

<div class="card p-3 mb-3">
    <h5><?php echo $step_name; ?></h5>

    <!-- ✅ QR CODE -->
    <img src="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?php echo urlencode($qr_link); ?>" />

    <p class="mt-2 small text-muted">
        Scan this QR at this station
    </p>

    <!-- DOWNLOAD BUTTON -->
    <a href="https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=<?php echo urlencode($qr_link); ?>" download class="btn btn-sm btn-primary mt-2">
        Download QR
    </a>
</div>

<?php endwhile; ?>

</body>
</html>