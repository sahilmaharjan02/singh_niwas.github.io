<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_rooms = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$active_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('pending', 'confirmed')")->fetchColumn();

$recent_bookings = $pdo->query("
    SELECT b.*, u.name as user_name, r.room_number, r.room_type 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN rooms r ON b.room_id = r.id 
    ORDER BY b.booking_date DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$recent_users = $pdo->query("
    SELECT * FROM users 
    ORDER BY created_at DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Singh Niwas</title>
    <!-- <style>
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

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--white);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px;
            background: var(--primary-color);
            color: var(--white);
            text-align: center;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }

        .sidebar-header p {
            margin: 5px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }

        .admin-info {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        .admin-info .avatar {
            width: 60px;
            height: 60px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            font-weight: bold;
        }

        .admin-info h4 {
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .admin-info p {
            color: #666;
            font-size: 13px;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s;
            gap: 12px;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--background-color);
            color: var(--primary-color);
            border-left: 4px solid var(--primary-color);
        }

        .nav-link ion-icon {
            font-size: 20px;
            width: 24px;
        }

        /* Main Content */
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

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stat-icon.users { background: var(--primary-color); }
        .stat-icon.rooms { background: var(--success); }
        .stat-icon.bookings { background: var(--warning); }
        .stat-icon.active { background: var(--info); }

        .stat-info h3 {
            font-size: 32px;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        /* Tables */
        .dashboard-section {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .section-header h3 {
            color: var(--text-dark);
            font-size: 18px;
        }

        .view-all {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }

        .view-all:hover {
            text-decoration: underline;
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

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #cce5ff; color: #004085; }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-sm {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }

        .btn-edit {
            background: var(--primary-color);
            color: white;
        }

        .btn-delete {
            background: var(--danger);
            color: white;
        }

        .btn-view {
            background: var(--info);
            color: white;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }
            
            .sidebar-header h2,
            .sidebar-header p,
            .admin-info h4,
            .admin-info p,
            .nav-link span {
                display: none;
            }
            
            .nav-link {
                justify-content: center;
                padding: 15px;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style> -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <?php include 'sidebar.php'; ?>
    
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p>Singh Niwas</p>
        </div>
        
        <div class="admin-info">
            <div class="avatar">
                <?php echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); ?>
            </div>
            <h4><?php echo htmlspecialchars($_SESSION['admin_name']); ?></h4>
            <p>Administrator</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="admin-dashboard.php" class="nav-link active">
                    <ion-icon name="grid-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-users.php" class="nav-link">
                    <ion-icon name="people-outline"></ion-icon>
                    <span>Manage Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-rooms.php" class="nav-link">
                    <ion-icon name="bed-outline"></ion-icon>
                    <span>Manage Rooms</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-bookings.php" class="nav-link">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>Manage Bookings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-bookings.php" class="nav-link">
                    <ion-icon name="cash-outline"></ion-icon>
                    <span>Manage Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-settings.php" class="nav-link">
                    <ion-icon name="settings-outline"></ion-icon>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <div class="header-actions">
                <span style="color: #666; font-size: 14px;">
                    Last login: Today
                </span>
                <form action="admin-logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        <ion-icon name="log-out-outline"></ion-icon>
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <ion-icon name="people-outline"></ion-icon>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon rooms">
                    <ion-icon name="bed-outline"></ion-icon>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_rooms; ?></h3>
                    <p>Total Rooms</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bookings">
                    <ion-icon name="calendar-outline"></ion-icon>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_bookings; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon active">
                    <ion-icon name="time-outline"></ion-icon>
                </div>
                <div class="stat-info">
                    <h3><?php echo $active_bookings; ?></h3>
                    <p>Active Bookings</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h3>Recent Bookings</h3>
                <a href="admin-bookings.php" class="view-all">View All →</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['room_type'] . ' - ' . $booking['room_number']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                        <td>NRS<?php echo $booking['total_amount']; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <!-- <button class="btn-sm btn-view" onclick="viewBooking(<?php echo $booking['id']; ?>)">
                                    View
                                </button> -->
                                <button class="btn-sm btn-edit" onclick="editBooking(<?php echo $booking['id']; ?>)">
                                    Edit
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h3>Recent Users</h3>
                <a href="admin-users.php" class="view-all">View All →</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recent_users as $user): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-sm btn-edit" onclick="editUser(<?php echo $user['id']; ?>)">
                                    Edit
                                </button>
                                <button class="btn-sm btn-delete" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function viewBooking(id) {
            window.location.href = 'admin-bookdetail.php?id=' + id;
        }

        function editBooking(id) {
            window.location.href = 'admin-edit-booking.php?id=' + id;
        }

        function editUser(id) {
            window.location.href = 'admin-edit-user.php?id=' + id;
        }

        function deleteUser(id) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                window.location.href = 'admin-delete-user.php?id=' + id;
            }
        }
    </script>
</body>
</html>