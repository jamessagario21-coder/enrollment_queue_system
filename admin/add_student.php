<?php
session_start();
require '../config.php';

/* =========================
   ACCESS CODE GENERATOR
========================= */
function generateAccessCode($conn) {

    $year = date("Y");

    $query = $conn->query("
        SELECT COUNT(*) as total 
        FROM students 
        WHERE access_code LIKE 'CDM-$year-%'
    ");

    $row = $query->fetch_assoc();
    $nextNumber = $row['total'] + 1;

    return "CDM-$year-" . str_pad($nextNumber, 4, "0", STR_PAD_LEFT);
}

/* =========================
   SEARCH ELIGIBLE STUDENTS
========================= */
$search = $_GET['search'] ?? '';

$stmt = $conn->prepare("
    SELECT id, full_name, program 
    FROM eligible_students
    WHERE full_name LIKE ?
    ORDER BY full_name ASC
");

$searchTerm = "%$search%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$eligible_result = $stmt->get_result();

/* =========================
   LAST ADDED STUDENT
========================= */
$new_student = null;
$error_message = null;

/* =========================
   MODE 1: MANUAL ADD
========================= */
if (isset($_POST['manual_add'])) {

    $student_id = trim($_POST['student_id']);
    $full_name = trim($_POST['full_name']);
    $program = trim($_POST['program']);

    /* CHECK DUPLICATE STUDENT ID */
    $check = $conn->prepare("
        SELECT student_id FROM students WHERE student_id = ?
    ");

    $check->bind_param("s", $student_id);
    $check->execute();
    $duplicate = $check->get_result();

    if ($duplicate->num_rows > 0) {

        $error_message = "Student ID already exists.";

    } else {

        $access_code = generateAccessCode($conn);

        $stmt = $conn->prepare("
            INSERT INTO students (student_id, full_name, program, access_code)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssss",
            $student_id,
            $full_name,
            $program,
            $access_code
        );

        $stmt->execute();

        /* FETCH INSERTED DATA (FIXED) */
        $stmt = $conn->prepare("
            SELECT * FROM students WHERE student_id = ?
        ");

        $stmt->bind_param("s", $student_id);
        $stmt->execute();

        $new_student = $stmt->get_result()->fetch_assoc();
    }
}

/* =========================
   MODE 2: FROM ELIGIBLE
========================= */
if (isset($_POST['add_from_eligible'])) {

    $eligible_id = (int)$_POST['eligible_id'];
    $program = trim($_POST['program']);

    $stmt = $conn->prepare("
        SELECT full_name 
        FROM eligible_students 
        WHERE id = ?
    ");

    $stmt->bind_param("i", $eligible_id);
    $stmt->execute();

    $q = $stmt->get_result()->fetch_assoc();

    if ($q) {

        $access_code = generateAccessCode($conn);

        $stmt = $conn->prepare("
            INSERT INTO students (student_id, full_name, program, access_code)
            VALUES (?, ?, ?, ?)
        ");

        /* IMPORTANT: eligible_students has NO student_id */
        $generated_student_id = "AUTO-" . time();

        $stmt->bind_param(
            "ssss",
            $generated_student_id,
            $q['full_name'],
            $program,
            $access_code
        );

        $stmt->execute();

        /* FETCH INSERTED DATA (FIXED) */
        $stmt = $conn->prepare("
            SELECT * FROM students WHERE student_id = ?
        ");

        $stmt->bind_param("s", $generated_student_id);
        $stmt->execute();

        $new_student = $stmt->get_result()->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student Queue</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .container-box {
            max-width: 1100px;
            margin: 30px auto;
        }

        .card-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 20px;
        }

        .section-title {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .table td {
            vertical-align: middle;
        }

        .header {
            background: #1d3557;
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>

<div class="container container-box">

    <!-- HEADER -->
    <div class="header d-flex justify-content-between align-items-center">

        <div>
            <h3 class="mb-0">🎓 Add Student System</h3>
            <small>Manual + Eligible Enrollment with CDM Access Code</small>
        </div>

        <div>
            <a href="dashboard.php" class="btn btn-light btn-sm">
                ⬅ Back to Dashboard
            </a>
        </div>

    </div>

    <!-- ERROR -->
    <?php if ($error_message): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error_message) ?>
        </div>

    <?php endif; ?>

    <!-- SUCCESS -->
    <?php if ($new_student): ?>

        <div class="card-box mb-3">

            <h5>🎉 Student Added Successfully</h5>

            <p>
                <b>Student ID:</b>
                <?= htmlspecialchars($new_student['student_id']) ?>
            </p>

            <p>
                <b>Name:</b>
                <?= htmlspecialchars($new_student['full_name']) ?>
            </p>

            <p>
                <b>Program:</b>
                <?= htmlspecialchars($new_student['program']) ?>
            </p>

            <p>
                <b>Access Code:</b>
                <code><?= htmlspecialchars($new_student['access_code']) ?></code>
            </p>

        </div>

    <?php endif; ?>

    <div class="row">

        <!-- MANUAL ADD -->
        <div class="col-md-5">

            <div class="card-box">

                <div class="section-title">
                    ➕ Manual Add Student
                </div>

                <form method="POST">

                    <input type="text"
                           name="student_id"
                           class="form-control mb-2"
                           placeholder="Student ID"
                           required>

                    <input type="text"
                           name="full_name"
                           class="form-control mb-2"
                           placeholder="Full Name"
                           required>

                    <select name="program"
                            class="form-select mb-3"
                            required>

                        <option value="">Select Program</option>
                        <option value="BSAR">BSAR</option>
                        <option value="BSCE">BSCE</option>
                        <option value="BSCpE">BSCpE</option>
                        <option value="BSEE">BSEE</option>
                        <option value="BSEcE">BSEcE</option>
                        <option value="BSENSE">BSENSE</option>
                        <option value="BSIE">BSIE</option>
                        <option value="BSME">BSME</option>

                    </select>

                    <button type="submit"
                            name="manual_add"
                            class="btn btn-primary w-100">

                        Add Student

                    </button>

                </form>

            </div>

        </div>

        <!-- ELIGIBLE -->
        <div class="col-md-7">

            <div class="card-box">

                <div class="section-title">
                    🔎 Search Eligible Students
                </div>

                <form method="GET" class="mb-3">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search name..."
                           value="<?= htmlspecialchars($search) ?>">

                </form>

                <table class="table table-hover">

                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php while($row = $eligible_result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                N/A
                            </td>

                            <td>
                                <?= htmlspecialchars($row['full_name']) ?>
                            </td>

                            <td>

                                <form method="POST" class="d-flex gap-2">

                                    <input type="hidden"
                                           name="eligible_id"
                                           value="<?= $row['id'] ?>">

                                    <select name="program"
                                            class="form-select form-select-sm"
                                            required>

                                        <option value="">Program</option>
                                        <option value="BSAR">BSAR</option>
                                        <option value="BSCE">BSCE</option>
                                        <option value="BSCpE">BSCpE</option>
                                        <option value="BSEE">BSEE</option>
                                        <option value="BSEcE">BSEcE</option>
                                        <option value="BSENSE">BSENSE</option>
                                        <option value="BSIE">BSIE</option>
                                        <option value="BSME">BSME</option>

                                    </select>

                            </td>

                            <td>

                                <button type="submit"
                                        name="add_from_eligible"
                                        class="btn btn-success btn-sm">

                                    Add to Queue

                                </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>