<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = "WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

$count_sql = "SELECT COUNT(*) FROM users $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

$sql = "SELECT * FROM users $where ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$active_count = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$inactive_count = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'inactive'")->fetchColumn();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $check_stmt->execute([$id]);
        $booking_count = $check_stmt->fetchColumn();
        
        if ($booking_count > 0) {
            $_SESSION['error'] = "Cannot delete user with existing bookings. Delete bookings first.";
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($delete_stmt->execute([$id])) {
                $_SESSION['success'] = "User deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete user.";
            }
        }
        header('Location: admin-users.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #f8f9fa;
            --text-dark: #002960;
            --white: #ffffff;
            --sidebar-width: 260px;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --gray: #6c757d;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
        }

        .header {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: var(--text-dark);
            font-size: 24px;
        }

        .action-bar {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            flex: 1;
            position: relative;
            min-width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .search-box ion-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-bar select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            min-width: 150px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-danger {
            background: var(--danger);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--text-dark);
        }

        .btn-secondary {
            background: var(--gray);
            color: white;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-dark);
        }

        .dashboard-section {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: var(--background-color);
            color: var(--text-dark);
            font-weight: 600;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            transition: all 0.3s ease;
        }

        .btn-sm:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        .page-link {
            padding: 8px 12px;
            background: var(--white);
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .page-link:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state ion-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ddd;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-bar {
                flex-direction: column;
            }
            
            .filter-bar select {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Manage Users</h1>
            <div>
                <a href="admin-dashboard.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <ion-icon name="checkmark-circle-outline"></ion-icon>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?php echo $total_users; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Users</h3>
                <div class="number" style="color: var(--success);"><?php echo $active_count; ?></div>
            </div>
            <div class="stat-card">
                <h3>Inactive Users</h3>
                <div class="number" style="color: var(--danger);"><?php echo $inactive_count; ?></div>
            </div>
        </div>

        <div class="action-bar">
            <form method="GET" class="search-box">
                <ion-icon name="search-outline"></ion-icon>
                <input type="text" name="search" placeholder="Search by name, email, or phone..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </form>
            
            <div class="filter-bar">
                <select name="status_filter" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
            </div>
            
            <!-- <button class="btn btn-primary" onclick="location.href='admin-add-user.php'">
                <ion-icon name="person-add-outline"></ion-icon>
                Add New User
            </button> -->
        </div>

        <div class="dashboard-section">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">
                Users (<?php echo $total_users; ?>)
            </h3>
            
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <ion-icon name="people-outline"></ion-icon>
                    <h3>No users found</h3>
                    <p><?php echo empty($search) && empty($status_filter) ? 'No users in the system yet.' : 'No users match your filters.'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $user['status'] ?? 'active'; ?>">
                                    <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <!-- <button class="btn-sm btn-primary" 
                                            onclick="location.href='admin-edit-user.php?id=<?php echo $user['id']; ?>'">
                                        <ion-icon name="create-outline"></ion-icon> Edit
                                    </button> -->
                                    <button class="btn-sm <?php echo ($user['status'] ?? 'active') == 'active' ? 'btn-warning' : 'btn-success'; ?>" 
                                            onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status'] ?? 'active'; ?>')">
                                        <ion-icon name="<?php echo ($user['status'] ?? 'active') == 'active' ? 'pause-circle-outline' : 'play-circle-outline'; ?>"></ion-icon>
                                        <?php echo ($user['status'] ?? 'active') == 'active' ? 'Deactivate' : 'Activate'; ?>
                                    </button>
                                    <!-- <button class="btn-sm btn-danger" 
                                            onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')">
                                        <ion-icon name="trash-outline"></ion-icon> Delete
                                    </button> -->
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status_filter=<?php echo urlencode($status_filter); ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function deleteUser(id, name) {
            if (confirm(`Are you sure you want to delete user "${name}"? This action cannot be undone.`)) {
                window.location.href = 'admin-users.php?delete=' + id;
            }
        }

        function toggleUserStatus(id, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            const action = newStatus === 'active' ? 'activate' : 'deactivate';
            
            if (confirm(`Are you sure you want to ${action} this user?`)) {
                window.location.href = `admin-toggle.php?id=${id}&status=${newStatus}`;
            }
        }

        document.querySelector('input[name="search"]').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                this.form.submit();
            }
        });
    </script>
</body>
</html>