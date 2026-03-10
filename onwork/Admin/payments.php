<?php
session_start();
require_once '../db_connection.php';

// Check admin authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_id = $_POST['payment_id'];
    $status = $_POST['status'];
    
    try {
        $pdo->beginTransaction();
        
        // Update payment status
        $stmt = $pdo->prepare("UPDATE payments SET status = ? WHERE id = ?");
        $stmt->execute([$status, $payment_id]);
        
        // If payment is completed, update booking payment status
        if ($status === 'completed') {
            $stmt = $pdo->prepare("
                UPDATE bookings b 
                JOIN payments p ON b.id = p.booking_id 
                SET b.payment_status = 'paid', b.status = 'confirmed' 
                WHERE p.id = ?
            ");
            $stmt->execute([$payment_id]);
            
            // Update room status to occupied
            $stmt = $pdo->prepare("
                UPDATE rooms r 
                JOIN bookings b ON r.id = b.room_id 
                JOIN payments p ON b.id = p.booking_id 
                SET r.status = 'occupied' 
                WHERE p.id = ?
            ");
            $stmt->execute([$payment_id]);
        }
        
        // If payment is refunded, update booking status
        if ($status === 'refunded') {
            $stmt = $pdo->prepare("
                UPDATE bookings b 
                JOIN payments p ON b.id = p.booking_id 
                SET b.payment_status = 'refunded', b.status = 'cancelled' 
                WHERE p.id = ?
            ");
            $stmt->execute([$payment_id]);
            
            // Update room status to available
            $stmt = $pdo->prepare("
                UPDATE rooms r 
                JOIN bookings b ON r.id = b.room_id 
                JOIN payments p ON b.id = p.booking_id 
                SET r.status = 'available' 
                WHERE p.id = ?
            ");
            $stmt->execute([$payment_id]);
        }
        
        $pdo->commit();
        
        $_SESSION['admin_message'] = 'Payment status updated successfully!';
        header('Location: payments.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['admin_error'] = 'Update failed: ' . $e->getMessage();
        header('Location: payments.php');
        exit();
    }
}

// Get all payments with filters
$status_filter = $_GET['status'] ?? '';
$method_filter = $_GET['method'] ?? '';

$query = "
    SELECT p.*, 
           u.name as user_name, 
           u.email as user_email,
           b.room_id,
           b.check_in,
           b.check_out,
           b.total_amount as booking_amount,
           r.room_number,
           r.room_type
    FROM payments p
    JOIN users u ON p.user_id = u.id
    JOIN bookings b ON p.booking_id = b.id
    JOIN rooms r ON b.room_id = r.id
";

$params = [];
$conditions = [];

if ($status_filter) {
    $conditions[] = "p.status = ?";
    $params[] = $status_filter;
}

if ($method_filter) {
    $conditions[] = "p.payment_method = ?";
    $params[] = $method_filter;
}

if (count($conditions) > 0) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY p.payment_date DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_payments = $pdo->query("SELECT COUNT(*) as count FROM payments")->fetch()['count'];
$total_amount = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'")->fetch()['total'] ?? 0;
$pending_count = $pdo->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Payment Management</title>
    <style>
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .amount {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-dark);
        }
        
        .filters {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: flex;
            gap: 20px;
            align-items: flex-end;
        }
        
        .filter-group {
            flex: 1;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .filter-btn, .reset-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .filter-btn {
            background: var(--primary-color);
            color: white;
        }
        
        .reset-btn {
            background: #6c757d;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        
        .action-form {
            display: flex;
            gap: 5px;
        }
        
        .status-select {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .update-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .message {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
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
            <h1>Payment Management</h1>
            <div class="header-actions">
                <form action="admin-logout.php" method="POST">
                    <button type="submit" class="logout-btn">
                        <ion-icon name="log-out-outline"></ion-icon>
                        Logout
                    </button>
                </form>
            </div>
        </div>
        
        <?php if (isset($_SESSION['admin_message'])): ?>
            <div class="message success">
                <?php echo $_SESSION['admin_message']; ?>
                <?php unset($_SESSION['admin_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['admin_error'])): ?>
            <div class="message error">
                <?php echo $_SESSION['admin_error']; ?>
                <?php unset($_SESSION['admin_error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Payments</h3>
                <div class="amount"><?php echo $total_payments; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <div class="amount">NRS <?php echo number_format($total_amount, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Payments</h3>
                <div class="amount"><?php echo $pending_count; ?></div>
            </div>
        </div>
        
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label>Payment Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                        <option value="refunded" <?php echo $status_filter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Payment Method</label>
                    <select name="method">
                        <option value="">All Methods</option>
                        <option value="esewa" <?php echo $method_filter === 'esewa' ? 'selected' : ''; ?>>eSewa</option>
                        <option value="khalti" <?php echo $method_filter === 'khalti' ? 'selected' : ''; ?>>Khalti</option>
                        <option value="cash" <?php echo $method_filter === 'cash' ? 'selected' : ''; ?>>Cash</option>
                        <option value="bank" <?php echo $method_filter === 'bank' ? 'selected' : ''; ?>>Bank Transfer</option>
                    </select>
                </div>
                
                <button type="submit" class="filter-btn">
                    <ion-icon name="filter-outline"></ion-icon>
                    Apply Filters
                </button>
                <a href="payments.php" class="reset-btn">
                    <ion-icon name="refresh-outline"></ion-icon>
                    Reset Filters
                </a>
            </form>
        </div>
        
        <div class="dashboard-section">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">
                Payment Records (<?php echo count($payments); ?>)
            </h3>
            
            <?php if (empty($payments)): ?>
                <div class="empty-state">
                    <ion-icon name="cash-outline"></ion-icon>
                    <h3>No payments found</h3>
                    <p><?php echo ($status_filter || $method_filter) ? 'No payments match your filters.' : 'No payments in the system yet.'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>User</th>
                            <th>Booking Details</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($payments as $payment): ?>
                        <tr>
                            <td>#<?php echo $payment['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($payment['user_name']); ?></strong><br>
                                <small><?php echo $payment['user_email']; ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($payment['room_type']); ?> - Room <?php echo $payment['room_number']; ?><br>
                                <small>Check-in: <?php echo date('d M Y', strtotime($payment['check_in'])); ?></small>
                            </td>
                            <td>NRS <?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo ucfirst($payment['payment_method']); ?></td>
                            <td><?php echo $payment['transaction_id'] ?: 'N/A'; ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $payment['status']; ?>">
                                    <?php echo ucfirst($payment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="" class="action-form">
                                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $payment['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="completed" <?php echo $payment['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="failed" <?php echo $payment['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        <option value="refunded" <?php echo $payment['status'] === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                    </select>
                                    <button type="submit" class="update-btn">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>