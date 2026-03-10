<?php

if (!isset($_SESSION)) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Singh Niwas</title>
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
        .status-available { background: #d4edda; color: #155724; }
        .status-occupied { background: #f8d7da; color: #721c24; }
        .status-maintenance { background: #fff3cd; color: #856404; }

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

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: var(--text-dark);
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn {
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }

        
        .form-container {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        
        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
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
        }

        .page-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
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

        
        .action-bar {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            flex: 1;
            position: relative;
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
        }

        .date-filters {
            display: flex;
            gap: 10px;
        }

        .date-filters input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .status-select {
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
            font-size: 12px;
        }

        
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .quick-stat {
            background: var(--white);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .quick-stat h4 {
            font-size: 24px;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .quick-stat p {
            color: #666;
            font-size: 12px;
        }

        
        .detail-card {
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .detail-value {
            flex: 1;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
        }

        .booking-id {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .price-breakdown {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            color: var(--success);
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .total-amount {
            font-weight: bold;
            color: var(--success);
        }

        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .room-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .password-note {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

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
            
            .date-inputs {
                grid-template-columns: 1fr;
            }
            
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-bar {
                flex-wrap: wrap;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p>Singh Niwas</p>
        </div>
        
        <div class="admin-info">
            <div class="avatar">
                <?php 
                if (isset($_SESSION['admin_name'])) {
                    echo strtoupper(substr($_SESSION['admin_name'], 0, 1)); 
                }
                ?>
            </div>
            <h4><?php echo isset($_SESSION['admin_name']) ? htmlspecialchars($_SESSION['admin_name']) : 'Admin'; ?></h4>
            <p>Administrator</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="admin-dashboard.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-dashboard.php' ? 'active' : ''; ?>">
                    <ion-icon name="grid-outline"></ion-icon>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-users.php' ? 'active' : ''; ?>">
                    <ion-icon name="people-outline"></ion-icon>
                    <span>Manage Users</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-rooms.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-rooms.php' ? 'active' : ''; ?>">
                    <ion-icon name="bed-outline"></ion-icon>
                    <span>Manage Rooms</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-bookings.php' ? 'active' : ''; ?>">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <span>Manage Bookings</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="payments.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
                     <ion-icon name="cash-outline"></ion-icon>
                     <span>Manage Payments</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="admin-settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin-settings.php' ? 'active' : ''; ?>">
                    <ion-icon name="settings-outline"></ion-icon>
                    <span>Settings</span>
                </a>
            </li>
        </ul>
    </div>