<?php
session_start();
require 'config.php';

// Handle login
$role_error = $login_error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $identifier = $_POST['identifier'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($role) || empty($identifier) || empty($password)) {
        $role_error = "Please fill all fields";
    } else {
        if ($role === 'admin') {
            // Admin login
            $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ? AND password = ?");
            $stmt->bind_param("ss", $identifier, $password);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_username'] = $identifier;
                header("Location: admin/dashboard.php");
                exit;
            } else {
                $login_error = "Invalid admin credentials";
            }
        } elseif ($role === 'student') {
            // Student view (no password needed, just ID)
            $stmt = $conn->prepare("SELECT id FROM students WHERE student_no = ?");
            $stmt->bind_param("s", $identifier);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                // FIXED: Redirect with student_no as GET parameter
                header("Location: student_view.php" . urlencode($identifier));
                exit;
            } else {
                $login_error = "Student number not found. Ask admin to add you to queue.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Queue System - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
        }
        .login-container { min-height: 100vh; }
        .choice-card { 
            transition: all 0.3s ease; 
            cursor: pointer; 
            height: 200px;
        }
        .choice-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.2) !important;
        }
        .choice-card.admin { border-top: 5px solid #dc3545; }
        .choice-card.student { border-top: 5px solid #28a745; }
    </style>
</head>
<body class="d-flex align-items-center login-container">
    <main class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10">
                <div class="row justify-content-center">
                    <!-- Role Selection Cards -->
                    <div class="col-md-5 mb-4">
                        <div class="card choice-card admin h-100 shadow-lg border-0 text-center p-4" onclick="selectRole('admin')">
                            <i class="fas fa-user-shield fa-4x text-danger mb-3"></i>
                            <h3 class="fw-bold mb-2">Admin Access</h3>
                            <p class="text-muted mb-0">Manage queue, update student progress</p>
                        </div>
                    </div>
                    <div class="col-md-5 mb-4">
                        <div class="card choice-card student h-100 shadow-lg border-0 text-center p-4" onclick="selectRole('student')">
                            <i class="fas fa-user-graduate fa-4x text-success mb-3"></i>
                            <h3 class="fw-bold mb-2">Student Access</h3>
                            <p class="text-muted mb-0">View your enrollment status</p>
                        </div>
                    </div>
                </div>

                <!-- Login Form (hidden initially) -->
                <div id="loginForm" class="row justify-content-center" style="display: none;">
                    <div class="col-lg-6 col-xl-5">
                        <div class="card shadow-lg border-0 overflow-hidden">
                            <div class="card-header bg-primary text-white text-center py-4">
                                <h2 class="mb-0 fw-bold" id="formTitle">Access Portal</h2>
                                <p class="mb-0 opacity-75" id="formSubtitle">Enter your details</p>
                            </div>
                            <div class="card-body p-5 p-lg-6">
                                <?php if($login_error): ?>
                                    <div class="alert alert-danger d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <?=$login_error?>
                                    </div>
                                <?php endif; ?>
                                <?php if($role_error): ?>
                                    <div class="alert alert-warning"><?=$role_error?></div>
                                <?php endif; ?>
                                
                                <form method="POST">
                                    <input type="hidden" name="role" id="selectedRole">
                                    
                                    <div class="mb-4">
                                        <label class="form-label fw-bold" id="identifierLabel">Identifier</label>
                                        <div class="input-group">
                                            <span class="input-group-text" id="iconPrefix">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <input type="text" class="form-control form-control-lg" 
                                                   name="identifier" id="identifierInput" required 
                                                   placeholder="Enter username or student number">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4" id="passwordField">
                                        <label class="form-label fw-bold">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control form-control-lg" 
                                                   name="password" required placeholder="Enter password">
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-lg w-100 btn-primary fw-bold py-3">
                                        <i class="fas fa-sign-in-alt me-2"></i>
                                        <span id="submitText">Continue</span>
                                    </button>
                                </form>
                                
                                <div class="text-center mt-4">
                                    <small class="text-muted">
                                        👨‍🎓 Students: Use your student number<br>
                                        👨‍💼 Admins: Use admin credentials
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        let selectedRole = '';
        
        function selectRole(role) {
            selectedRole = role;
            document.getElementById('selectedRole').value = role;
            document.getElementById('loginForm').style.display = 'block';
            
            // Update form based on role
            if (role === 'admin') {
                document.getElementById('formTitle').innerHTML = '<i class="fas fa-user-shield me-2"></i>Admin Login';
                document.getElementById('formSubtitle').innerHTML = 'Manage enrollment queue';
                document.getElementById('identifierLabel').innerHTML = 'Username';
                document.getElementById('identifierInput').placeholder = 'Enter admin username';
                document.getElementById('iconPrefix').innerHTML = '<i class="fas fa-user-shield"></i>';
                document.getElementById('passwordField').style.display = 'block';
                document.getElementById('submitText').innerHTML = 'Admin Login';
            } else if (role === 'student') {
                document.getElementById('formTitle').innerHTML = '<i class="fas fa-user-graduate me-2"></i>Student Status';
                document.getElementById('formSubtitle').innerHTML = 'Check your enrollment progress';
                document.getElementById('identifierLabel').innerHTML = 'Student Number';
                document.getElementById('identifierInput').placeholder = 'Enter your student number';
                document.getElementById('iconPrefix').innerHTML = '<i class="fas fa-id-card"></i>';
                document.getElementById('passwordField').style.display = 'none';
                document.querySelector('input[name="password"]').removeAttribute('required');
                document.getElementById('submitText').innerHTML = 'View Status';
            }
            
            // Smooth scroll to form
            document.getElementById('loginForm').scrollIntoView({ behavior: 'smooth' });
        }
    </script>
</body>
</html>
