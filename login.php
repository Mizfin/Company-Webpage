<?php
require_once 'php/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if (empty($email) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {
        $hashed     = hash('sha256', $password);
        $email_safe = mysqli_real_escape_string($conn, $email);
        $role_safe  = ($role === 'admin') ? 'admin' : 'employee';

        // Fetch user by email + password + role (any status)
        $sql    = "SELECT * FROM users WHERE email='$email_safe' AND password='$hashed' AND role='$role_safe'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

            // Check account status
            if ($user['status'] === 'pending') {
                $error = "⏳ Your account is <strong>pending admin approval</strong>. Please wait until an admin reviews your registration.";
            } elseif ($user['status'] === 'rejected') {
                $error = "❌ Your account has been <strong>rejected</strong> by the admin. Please contact the administrator.";
            } else {
                // Approved — start session and redirect
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['fullname'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                $_SESSION['user_dept']  = $user['department'];

                if ($user['role'] === 'admin') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: employee/dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Invalid email, password, or role selected.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Al-Hind Solutions</title>
    <link rel="stylesheet" href="css/style.css?v=2">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">🏢 Al-Hind Solutions</div>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="login.php" class="active">Login</a></li>
            <li><a href="register.php">Register</a></li>
        </ul>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">
            <h2>🔐 Welcome Back</h2>
            <p class="auth-subtitle">Login to your Al-Hind Solutions account</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">

                <!-- Role Tabs -->
                <div class="role-tabs">
                    <input type="radio" name="role" id="tab-employee" value="employee"
                           <?= (!isset($_POST['role']) || $_POST['role']==='employee') ? 'checked' : '' ?>>
                    <label for="tab-employee">👤 Employee Login</label>

                    <input type="radio" name="role" id="tab-admin" value="admin"
                           <?= (isset($_POST['role']) && $_POST['role']==='admin') ? 'checked' : '' ?>>
                    <label for="tab-admin">🔑 Admin Login</label>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="Enter your email" required
                           value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-primary btn-full">🔓 Login</button>

                <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>

                <!-- Demo credentials -->
                <div style="margin-top:18px;padding:14px;background:#f8fafc;border-radius:8px;font-size:0.85rem;color:#64748b;border:1px solid #e2e8f0;">
                    <strong>🧪 Demo Credentials:</strong><br>
                    🔑 Admin &nbsp;&nbsp;— admin@al-hindsolutions.com &nbsp;/ admin123<br>
                    👤 Employee — alice@al-hindsolutions.com &nbsp;&nbsp;/ employee123
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Al-Hind Solutions Pvt. Ltd. All rights reserved.</p>
    </footer>
    <script src="js/script.js"></script>
</body>
</html>
