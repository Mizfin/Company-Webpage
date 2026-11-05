<?php
require_once '../php/config.php';

// Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ── Handle Approve / Reject / Delete actions ──
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $action_id = (int)$_GET['id'];
    $action    = $_GET['action'];

    if ($action === 'approve') {
        mysqli_query($conn, "UPDATE users SET status='approved' WHERE id=$action_id");
        header("Location: dashboard.php?msg=approved");
        exit();
    } elseif ($action === 'reject') {
        mysqli_query($conn, "UPDATE users SET status='rejected' WHERE id=$action_id");
        header("Location: dashboard.php?msg=rejected");
        exit();
    } elseif ($action === 'delete' && $action_id !== (int)$_SESSION['user_id']) {
        mysqli_query($conn, "DELETE FROM users WHERE id=$action_id");
        header("Location: dashboard.php?msg=deleted");
        exit();
    }
}

// ── Fetch users grouped by status ──
$pending_result  = mysqli_query($conn, "SELECT * FROM users WHERE status='pending'  ORDER BY created_at DESC");
$approved_result = mysqli_query($conn, "SELECT * FROM users WHERE status='approved' ORDER BY created_at DESC");
$rejected_result = mysqli_query($conn, "SELECT * FROM users WHERE status='rejected' ORDER BY created_at DESC");

$pending_users  = mysqli_fetch_all($pending_result,  MYSQLI_ASSOC);
$approved_users = mysqli_fetch_all($approved_result, MYSQLI_ASSOC);
$rejected_users = mysqli_fetch_all($rejected_result, MYSQLI_ASSOC);

$total_pending  = count($pending_users);
$total_approved = count($approved_users);
$total_rejected = count($rejected_users);
$total_employees = count(array_filter($approved_users, fn($u) => $u['role'] === 'employee'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Al-Hind Solutions</title>
    <link rel="stylesheet" href="../css/style.css?v=2">
    <style>
        /* Approval-specific styles */
        .pending-badge  { background:#fef3c7; color:#92400e; }
        .approved-badge { background:#d1fae5; color:#065f46; }
        .rejected-badge { background:#fee2e2; color:#991b1b; }

        .btn-approve {
            background: #d1fae5;
            color: #065f46;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }
        .btn-approve:hover { background: #10b981; color: white; }

        .btn-reject {
            background: #fee2e2;
            color: #991b1b;
            border: none;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
            margin-left: 6px;
        }
        .btn-reject:hover { background: #ef4444; color: white; }

        .pending-banner {
            background: linear-gradient(135deg, #fef3c7, #fffbeb);
            border: 2px solid #f59e0b;
            border-radius: 12px;
            padding: 18px 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }
        .pending-banner .pb-icon { font-size: 2rem; }
        .pending-banner h3 { color: #92400e; margin-bottom: 2px; }
        .pending-banner p  { color: #78350f; font-size: 0.9rem; }

        .section-label {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
            margin: 28px 0 12px;
        }

        .stat-red { background: linear-gradient(135deg, #ef4444, #f87171); }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="nav-brand">🏢 Al-Hind Admin</div>
        <ul class="nav-links">
            <li><span class="nav-user">👋 <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <li><a href="../php/logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>

    <div class="dashboard-wrapper">

        <!-- Sidebar -->
        <aside class="sidebar">
            <ul>
                <li class="active"><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="#pending-section">⏳ Pending Approvals <?php if($total_pending > 0): ?><span style="background:#ef4444;color:#fff;border-radius:50%;padding:1px 7px;font-size:0.75rem;margin-left:4px;"><?= $total_pending ?></span><?php endif; ?></a></li>
                <li><a href="#all-users">👥 All Users</a></li>
                <li><a href="../index.html">🌐 View Website</a></li>
                <li><a href="../php/logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="dashboard-main">
            <h1 class="dashboard-title">🔑 Admin Dashboard</h1>

            <!-- Flash messages -->
            <?php if (isset($_GET['msg'])): ?>
                <?php
                    $msgs = [
                        'approved' => ['✅ User approved successfully! They can now login.', 'success'],
                        'rejected' => ['🚫 User has been rejected.', 'error'],
                        'deleted'  => ['🗑️ User deleted successfully.', 'success'],
                    ];
                    $m = $msgs[$_GET['msg']] ?? null;
                    if ($m): ?>
                <div class="alert alert-<?= $m[1] ?>"><?= $m[0] ?></div>
            <?php endif; endif; ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card stat-blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3><?= $total_approved ?></h3>
                        <p>Approved Users</p>
                    </div>
                </div>
                <div class="stat-card stat-green">
                    <div class="stat-icon">👤</div>
                    <div class="stat-info">
                        <h3><?= $total_employees ?></h3>
                        <p>Employees</p>
                    </div>
                </div>
                <div class="stat-card stat-orange">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3><?= $total_pending ?></h3>
                        <p>Pending Approval</p>
                    </div>
                </div>
                <div class="stat-card stat-red">
                    <div class="stat-icon">🚫</div>
                    <div class="stat-info">
                        <h3><?= $total_rejected ?></h3>
                        <p>Rejected</p>
                    </div>
                </div>
            </div>

            <!-- ── PENDING APPROVALS SECTION ── -->
            <div id="pending-section" style="margin-top:30px;">
                <?php if ($total_pending > 0): ?>
                    <div class="pending-banner">
                        <div class="pb-icon">⏳</div>
                        <div>
                            <h3><?= $total_pending ?> Registration(s) Waiting for Approval</h3>
                            <p>Review and approve or reject new employee/admin registrations below.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="table-card">
                    <div class="table-header">
                        <h2>⏳ Pending Approvals</h2>
                        <span class="badge badge-orange"><?= $total_pending ?> pending</span>
                    </div>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Department</th>
                                    <th>Phone</th>
                                    <th>Applied On</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pending_users)): ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; color:#64748b; padding:24px;">
                                        ✅ No pending approvals at this time.
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($pending_users as $i => $user): ?>
                                <tr style="background:#fffbeb;">
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($user['fullname']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $user['role'] === 'admin' ? 'orange' : 'green' ?>">
                                            <?= $user['role'] === 'admin' ? '🔑 Admin' : '👤 Employee' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($user['department']) ?></td>
                                    <td><?= htmlspecialchars($user['phone']) ?: '—' ?></td>
                                    <td><?= date('d M Y, h:i A', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="dashboard.php?action=approve&id=<?= $user['id'] ?>"
                                           class="btn-approve"
                                           onclick="return confirm('Approve <?= htmlspecialchars($user['fullname']) ?>?')">
                                           ✅ Approve
                                        </a>
                                        <a href="dashboard.php?action=reject&id=<?= $user['id'] ?>"
                                           class="btn-reject"
                                           onclick="return confirm('Reject <?= htmlspecialchars($user['fullname']) ?>?')">
                                           ❌ Reject
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ── ALL APPROVED USERS ── -->
            <p class="section-label" id="all-users">✅ Approved Users</p>
            <div class="table-card">
                <div class="table-header">
                    <h2>👥 Approved Users</h2>
                    <a href="../register.php" class="btn-primary btn-sm">+ Add User</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Phone</th>
                                <th>Joined</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_users as $i => $user): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($user['fullname']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'orange' : 'green' ?>">
                                        <?= $user['role'] === 'admin' ? '🔑 Admin' : '👤 Employee' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['department']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?: '—' ?></td>
                                <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="dashboard.php?action=reject&id=<?= $user['id'] ?>"
                                           class="btn-reject" style="margin-left:0;"
                                           onclick="return confirm('Revoke access for <?= htmlspecialchars($user['fullname']) ?>?')">
                                           🚫 Revoke
                                        </a>
                                        <a href="dashboard.php?action=delete&id=<?= $user['id'] ?>"
                                           class="btn-delete"
                                           onclick="return confirm('Permanently delete <?= htmlspecialchars($user['fullname']) ?>?')">
                                           🗑 Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#aaa;font-size:0.85rem;">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ── REJECTED USERS ── -->
            <?php if (!empty($rejected_users)): ?>
            <p class="section-label">🚫 Rejected Users</p>
            <div class="table-card">
                <div class="table-header">
                    <h2>🚫 Rejected Users</h2>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;"><?= $total_rejected ?> rejected</span>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Department</th>
                                <th>Applied On</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rejected_users as $i => $user): ?>
                            <tr style="background:#fff5f5;">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($user['fullname']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $user['role'] === 'admin' ? 'orange' : 'green' ?>">
                                        <?= $user['role'] === 'admin' ? '🔑 Admin' : '👤 Employee' ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($user['department']) ?></td>
                                <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <a href="dashboard.php?action=approve&id=<?= $user['id'] ?>"
                                       class="btn-approve"
                                       onclick="return confirm('Approve this user?')">
                                       ✅ Approve
                                    </a>
                                    <a href="dashboard.php?action=delete&id=<?= $user['id'] ?>"
                                       class="btn-delete"
                                       onclick="return confirm('Permanently delete this user?')">
                                       🗑 Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Al-Hind Solutions Pvt. Ltd. All rights reserved.</p>
    </footer>
</body>
</html>
