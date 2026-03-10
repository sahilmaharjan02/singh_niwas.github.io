<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = 'WHERE 1=1';
$params = [];

if (!empty($search)) {
    $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR r.room_number LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($status_filter)) {
    $where .= " AND b.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_from)) {
    $where .= " AND b.check_in >= ?";
    $params[] = $date_from;
}

if (!empty($date_to)) {
    $where .= " AND b.check_out <= ?";
    $params[] = $date_to;
}

$count_sql = "SELECT COUNT(*) FROM bookings b 
              JOIN users u ON b.user_id = u.id 
              JOIN rooms r ON b.room_id = r.id 
              $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_bookings = $count_stmt->fetchColumn();
$total_pages = ceil($total_bookings / $limit);

$sql = "SELECT b.*, u.name as user_name, u.email as user_email, 
               r.room_number, r.room_type, r.price_per_night 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN rooms r ON b.room_id = r.id 
        $where 
        ORDER BY b.booking_date DESC 
        LIMIT $limit OFFSET $offset";
        
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

if (isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $update_stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    if ($update_stmt->execute([$new_status, $booking_id])) {
        $_SESSION['success'] = "Booking status updated successfully!";
    } else {
        $_SESSION['error'] = "Failed to update booking status.";
    }
    header('Location: admin-bookings.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - Singh Niwas</title>
    <style>
        
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

        .total-amount {
            font-weight: bold;
            color: var(--success);
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
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Manage Bookings</h1>
            <div>
                <a href="admin-dashboard.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php 
        $stats_sql = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(total_amount) as total_revenue
            FROM bookings";
        $stats = $pdo->query($stats_sql)->fetch();
        ?>
        
        <div class="quick-stats">
            <div class="quick-stat">
                <h4><?php echo $stats['total']; ?></h4>
                <p>Total Bookings</p>
            </div>
            <div class="quick-stat">
                <h4><?php echo $stats['pending']; ?></h4>
                <p>Pending</p>
            </div>
            <div class="quick-stat">
                <h4><?php echo $stats['confirmed']; ?></h4>
                <p>Confirmed</p>
            </div>
            <div class="quick-stat">
                <h4>NRS <?php echo number_format($stats['total_revenue'] ?? 0, 2); ?></h4>
                <p>Total Revenue</p>
            </div>
        </div>

        <div class="action-bar">
            <form method="GET" class="search-box">
                <ion-icon name="search-outline"></ion-icon>
                <input type="text" name="search" placeholder="Search by customer, room..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </form>
            
            <form method="GET" class="filter-bar">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                
                <div class="date-filters">
                    <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                           placeholder="From Date" onchange="this.form.submit()">
                    <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                           placeholder="To Date" onchange="this.form.submit()">
                </div>
                
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <input type="hidden" name="page" value="1">
            </form>
            
            <button class="btn btn-primary" onclick="location.href='admin-add-booking.php'">
                <ion-icon name="add-circle-outline"></ion-icon>
                Add Booking
            </button>
        </div>

        <div class="dashboard-section">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">
                Bookings (<?php echo $total_bookings; ?>)
            </h3>
            
            <?php if (empty($bookings)): ?>
                <div class="empty-state">
                    <ion-icon name="calendar-outline"></ion-icon>
                    <h3>No bookings found</h3>
                    <p><?php echo empty($search) ? 'No bookings in the system yet.' : 'No bookings match your search.'; ?></p>
                </div>
            <?php else: ?>
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
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['user_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($booking['user_email']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($booking['room_type']); ?><br>
                                <small>Room #<?php echo htmlspecialchars($booking['room_number']); ?></small>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                            <td class="total-amount">NRS <?php echo number_format($booking['total_amount'], 2); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <?php if ($booking['payment_status'] == 'paid'): ?>
                                    <span style="color: var(--success); font-weight: bold;">
                                        <ion-icon name="checkmark-circle"></ion-icon> Paid
                                    </span>
                                <?php elseif ($booking['payment_status'] == 'pending'): ?>
                                    <span style="color: var(--warning);">
                                        <ion-icon name="time-outline"></ion-icon> Pending
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--danger);">
                                        <ion-icon name="close-circle"></ion-icon> Refunded
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <!-- <button class="btn-sm btn-success" 
                                            onclick="location.href='admin-bookdetail.php?id=<?php echo $booking['id']; ?>'">
                                        <ion-icon name="eye-outline"></ion-icon> View
                                    </button> -->
                                    <button class="btn-sm btn-primary" 
                                            onclick="location.href='admin-edit-booking.php?id=<?php echo $booking['id']; ?>'">
                                        <ion-icon name="create-outline"></ion-icon> Update
                                    </button>
                                    <button class="btn-sm btn-danger" 
                                            onclick="deleteBooking(<?php echo $booking['id']; ?>)">
                                        <ion-icon name="trash-outline"></ion-icon> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" 
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
        function deleteBooking(id) {
            if (confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
                window.location.href = 'admin-delete-booking.php?id=' + id;
            }
        }
        
        document.querySelector('input[name="date_to"]').max = new Date().toISOString().split('T')[0];
        
        document.querySelector('input[name="date_from"]').addEventListener('change', function() {
            document.querySelector('input[name="date_to"]').min = this.value;
        });
    </script>
</body>
</html>