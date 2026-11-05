<?php
require_once 'php/config.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname   = trim($_POST['fullname']);
    $email      = trim($_POST['email']);
    $password   = $_POST['password'];
    $confirm    = $_POST['confirm_password'];
    $department = trim($_POST['department']);
    $phone      = trim($_POST['phone']);
    $role       = $_POST['role'];

    // Basic validation
    if (empty($fullname) || empty($email) || empty($password) || empty($department)) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check duplicate email
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email='".mysqli_real_escape_string($conn, $email)."'");
        if (mysqli_num_rows($check) > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hashed     = hash('sha256', $password);
            $role_clean = ($role === 'admin') ? 'admin' : 'employee';

            // All new registrations start as 'pending' — admin must approve
            $sql = "INSERT INTO users (fullname, email, password, role, status, department, phone)
                    VALUES (
                        '".mysqli_real_escape_string($conn, $fullname)."',
                        '".mysqli_real_escape_string($conn, $email)."',
                        '$hashed',
                        '$role_clean',
                        'pending',
                        '".mysqli_real_escape_string($conn, $department)."',
                        '".mysqli_real_escape_string($conn, $phone)."'
                    )";
            if (mysqli_query($conn, $sql)) {
                $success = "pending";  // trigger the pending notice UI
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title>Register | Al-Hind Solutions</title>
    <link rel="stylesheet" href="css/style.css?v=2">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">🏢 Al-Hind Solutions</div>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="register.php" class="active">Register</a></li>
        </ul>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">

            <?php if ($success === 'pending'): ?>
            <!-- ── Registration Pending Screen ── -->
            <div style="text-align:center; padding: 20px 0;">
                <div style="font-size:4rem; margin-bottom:16px;">⏳</div>
                <h2 style="color:#92400e; margin-bottom:10px;">Registration Submitted!</h2>
                <p style="color:#64748b; line-height:1.7; margin-bottom:20px;">
                    Your account has been created and is <strong>pending admin approval</strong>.<br>
                    You will be able to login once an admin reviews and approves your request.
                </p>
                <div style="background:#fef3c7; border:1px solid #f59e0b; border-radius:10px; padding:16px; margin-bottom:24px; text-align:left; font-size:0.9rem; color:#78350f;">
                    <strong>⚠️ What happens next?</strong><br><br>
                    1. Admin reviews your registration.<br>
                    2. If approved — you can login with your credentials.<br>
                    3. If rejected — your account will be declined.
                </div>
                <a href="login.php" class="btn-primary" style="display:inline-block;">Go to Login Page</a>
            </div>

            <?php else: ?>
            <!-- ── Registration Form ── -->
            <h2>📋 Create Account</h2>
            <p class="auth-subtitle">Join Al-Hind Solutions today</p>

            <?php if ($error): ?>
                <div class="alert alert-error">❌ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php" onsubmit="return validateRegister()">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="fullname" placeholder="e.g. John Doe" required
                               value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="email" placeholder="e.g. john@example.com" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" id="reg_password" placeholder="Min. 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" id="reg_confirm" placeholder="Repeat password" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department" required>
                            <option value="">-- Select Department --</option>
                            <option value="IT"         <?= (isset($_POST['department']) && $_POST['department']==='IT')         ? 'selected' : '' ?>>IT</option>
                            <option value="HR"         <?= (isset($_POST['department']) && $_POST['department']==='HR')         ? 'selected' : '' ?>>HR</option>
                            <option value="Finance"    <?= (isset($_POST['department']) && $_POST['department']==='Finance')    ? 'selected' : '' ?>>Finance</option>
                            <option value="Marketing"  <?= (isset($_POST['department']) && $_POST['department']==='Marketing')  ? 'selected' : '' ?>>Marketing</option>
                            <option value="Management" <?= (isset($_POST['department']) && $_POST['department']==='Management') ? 'selected' : '' ?>>Management</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="e.g. 9876543210"
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Register As *</label>
                    <div class="role-select">
                        <label class="role-option">
                            <input type="radio" name="role" value="employee"
                                   <?= (!isset($_POST['role']) || $_POST['role']==='employee') ? 'checked' : '' ?>>
                            <span>👤 Employee</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="admin"
                                   <?= (isset($_POST['role']) && $_POST['role']==='admin') ? 'checked' : '' ?>>
                            <span>🔑 Admin</span>
                        </label>
                    </div>
                </div>

                <!-- Approval notice -->
                <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:12px 14px; margin-bottom:18px; font-size:0.88rem; color:#1e40af;">
                    ℹ️ <strong>Note:</strong> New accounts require <strong>admin approval</strong> before you can login.
                </div>

                <button type="submit" class="btn-primary btn-full">✅ Submit Registration</button>
                <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
            </form>
            <?php endif; ?>

        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Al-Hind Solutions Pvt. Ltd. All rights reserved.</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
