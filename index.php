<?php
require 'config.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $role = $_POST['role'] ?? '';
    $username = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    /* =========================
       ADMIN LOGIN
    ========================= */
    if ($role === 'admin') {

        if (empty($username) || empty($password)) {

            $error = 'Please fill all admin fields.';

        } else {

            $stmt = $conn->prepare("
                SELECT admin_id, username
                FROM admins
                WHERE username = ? AND password = ?
                LIMIT 1
            ");

            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $admin = $result->fetch_assoc();

                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];

                session_regenerate_id(true);

                header("Location: admin/dashboard.php");
                exit;

            } else {

                $error = "❌ Invalid admin credentials.";

            }

            $stmt->close();
        }
    }

    /* =========================
       STUDENT LOGIN
       ACCESS CODE + BIRTHDATE
    ========================= */
    else if ($role === 'student') {

        $birthdate = trim($_POST['birthdate'] ?? '');

        if (empty($username) || empty($birthdate)) {

            $error = 'Please enter Access Code and Birthdate.';

        } else {

            $stmt = $conn->prepare("
                SELECT student_id, full_name, program, access_code, birthdate
                FROM students
                WHERE access_code = ?
                AND birthdate = ?
                LIMIT 1
            ");

            $stmt->bind_param("ss", $username, $birthdate);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {

                $student = $result->fetch_assoc();

                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['access_code'] = $student['access_code'];
                $_SESSION['student_name'] = $student['full_name'];

                session_regenerate_id(true);

                header("Location: student_view.php?access_code=" . urlencode($student['access_code']));
                exit;

            } else {

                $error = "❌ Invalid Access Code or Birthdate.";

            }

            $stmt->close();
        }
    }

    else {

        $error = 'Invalid role selected.';

    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Queue System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>

<body style="background: linear-gradient(135deg, #1e3c72, #2a5298); min-height:100vh; font-family:Poppins,sans-serif;">

<div class="container d-flex align-items-center justify-content-center min-vh-100">

    <div class="col-lg-6">

        <!-- ERROR -->
        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <!-- MAIN CARD -->
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">

            <!-- HEADER -->
            <div class="text-center p-4 text-white"
                 style="background: linear-gradient(135deg,#4facfe,#00f2fe);">

                <i class="fas fa-school fa-3x mb-3"></i>

                <h3 class="fw-bold mb-1">
                    Enrollment System
                </h3>

                <p class="mb-0">
                    Welcome! Please select access type
                </p>

            </div>

            <!-- BODY -->
            <div class="card-body p-4">

                <!-- ROLE SELECTION -->
                <div id="roleSelection">

                    <button class="btn btn-primary w-100 mb-3"
                            onclick="showStudent()">

                        👨‍🎓 Student Access

                    </button>

                    <button class="btn btn-danger w-100"
                            onclick="showAdmin()">

                        👨‍💼 Admin Access

                    </button>

                </div>

                <!-- =========================
                     STUDENT FORM
                ========================= -->
                <form method="POST"
                      id="studentForm"
                      style="display:none;">

                    <input type="hidden"
                           name="role"
                           value="student">

                    <h5 class="text-center mb-3">
                        Student Login
                    </h5>

                    <input type="text"
                           name="identifier"
                           class="form-control mb-3"
                           placeholder="Access Code (e.g. CDM-2026-0001)"
                           required>

                    <input type="date"
                           name="birthdate"
                           class="form-control mb-3"
                           required>

                    <button class="btn btn-success w-100">
                        View Status
                    </button>

                    <button type="button"
                            class="btn btn-link w-100 mt-2"
                            onclick="goBack()">

                        ← Back

                    </button>

                </form>

                <!-- =========================
                     ADMIN FORM
                ========================= -->
                <form method="POST"
                      id="adminForm"
                      style="display:none;">

                    <input type="hidden"
                           name="role"
                           value="admin">

                    <h5 class="text-center mb-3">
                        Admin Login
                    </h5>

                    <input type="text"
                           name="identifier"
                           class="form-control mb-3"
                           placeholder="Username"
                           required>

                    <input type="password"
                           name="password"
                           class="form-control mb-3"
                           placeholder="Password"
                           required>

                    <button class="btn btn-danger w-100">
                        Login
                    </button>

                    <button type="button"
                            class="btn btn-link w-100 mt-2"
                            onclick="goBack()">

                        ← Back

                    </button>

                </form>

            </div>
        </div>

        <!-- FOOTER -->
        <p class="text-center text-white-50 mt-4">
            © <?= date("Y") ?> Enrollment System | All rights reserved
        </p>

    </div>
</div>

<script>
function showStudent() {

    document.getElementById("roleSelection").style.display = "none";
    document.getElementById("studentForm").style.display = "block";
}

function showAdmin() {

    document.getElementById("roleSelection").style.display = "none";
    document.getElementById("adminForm").style.display = "block";
}

function goBack() {

    document.getElementById("roleSelection").style.display = "block";
    document.getElementById("studentForm").style.display = "none";
    document.getElementById("adminForm").style.display = "none";
}
</script>

</body>
</html>