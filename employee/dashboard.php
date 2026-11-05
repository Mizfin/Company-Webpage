<?php
require_once '../php/config.php';

// Only employees can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}

// Fetch logged-in user's data
$id     = (int)$_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id=$id");
$user   = mysqli_fetch_assoc($result);

// Handle profile update
$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $dept  = mysqli_real_escape_string($conn, trim($_POST['department']));
    mysqli_query($conn, "UPDATE users SET phone='$phone', department='$dept' WHERE id=$id");
    $_SESSION['user_dept'] = $dept;
    $update_msg            = "Profile updated successfully!";
    $user['phone']         = $phone;
    $user['department']    = $dept;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Al-Hind Solutions</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">🚀 Al-Hind Solutions</div>
        <ul class="nav-links">
            <li><span class="nav-user">👋 <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <li><a href="../php/logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="dashboard-wrapper">

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li class="active"><a href="dashboard.php">🏠 My Dashboard</a></li>
                <li><a href="#profile-section">👤 My Profile</a></li>
                <li><a href="../index.html">🌐 View Website</a></li>
                <li><a href="../php/logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <h1 class="dashboard-title">👤 Employee Dashboard</h1>

            <!-- Welcome Card -->
            <div class="welcome-card">
                <div class="welcome-avatar">
                    <?= strtoupper(substr($user['fullname'], 0, 1)) ?>
                </div>
                <div class="welcome-info">
                    <h2>Hello, <?= htmlspecialchars($user['fullname']) ?>! 👋</h2>
                    <p>📧 <?= htmlspecialchars($user['email']) ?></p>
                    <p>🏢 Department: <strong><?= htmlspecialchars($user['department']) ?></strong></p>
                    <p>📅 Member since: <?= date('d M Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>

            <!-- Stats -->
            <div class="stats-grid" style="margin-top:24px;">
                <div class="stat-card stat-blue">
                    <div class="stat-icon">🏢</div>
                    <div class="stat-info">
                        <h3><?= htmlspecialchars($user['department']) ?></h3>
                        <p>My Department</p>
                    </div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">👤</div>
                    <div class="stat-info">
                        <h3>Employee</h3>
                        <p>My Role</p>
                    </div>
                </div>
                <div class="stat-card stat-orange">
                    <div class="stat-icon">📅</div>
                    <div class="stat-info">
                        <h3><?= date('Y', strtotime($user['created_at'])) ?></h3>
                        <p>Joined Year</p>
                    </div>
                </div>
            </div>

            <!-- Profile Update -->
            <div class="table-card" id="profile-section" style="margin-top:28px;">
                <div class="table-header">
                    <h2>✏️ Update My Profile</h2>
                </div>

                <?php if ($update_msg): ?>
                    <div class="alert alert-success" style="margin:16px 24px 0;">✅ <?= $update_msg ?></div>
                <?php endif; ?>

                <form method="POST" action="dashboard.php" style="padding:20px 24px;">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name (read-only)</label>
                            <input type="text" value="<?= htmlspecialchars($user['fullname']) ?>" disabled>
                        </div>
                        <div class="form-group">
                            <label>Email (read-only)</label>
                            <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <select name="department">
                                <?php
                                $depts = ['IT','HR','Finance','Marketing','Management'];
                                foreach ($depts as $d) {
                                    $sel = ($d === $user['department']) ? 'selected' : '';
                                    echo "<option value='$d' $sel>$d</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone"
                                   value="<?= htmlspecialchars($user['phone']) ?>"
                                   placeholder="e.g. 9876543210">
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">💾 Save Changes</button>
                </form>
            </div>

        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2025 NovaTech Solutions Pvt. Ltd. All rights reserved.</p>
    </footer>
</body>
</html>
