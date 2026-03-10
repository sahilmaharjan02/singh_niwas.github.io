<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get payment history
$stmt = $pdo->prepare("
    SELECT p.*, b.room_id, b.check_in, b.check_out, r.room_number, r.room_type 
    FROM payments p 
    JOIN bookings b ON p.booking_id = b.id 
    JOIN rooms r ON b.room_id = r.id 
    WHERE p.user_id = ? 
    ORDER BY p.payment_date DESC
");
$stmt->execute([$user_id]);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #e6f2ff;
            --text-dark: #002960;
            --white: #ffffff;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--background-color);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: var(--text-dark);
        }
        
        .back-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            color: var(--text-dark);
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-card .amount {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .payments-table {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
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
        
        .status-completed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-refunded { background: #cce5ff; color: #004085; }
        
        .method-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            background: #e9ecef;
            color: #495057;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .action-buttons {
    display: flex;
    gap: 5px;
    align-items: center;
}

.action-buttons a {
    text-decoration: none;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 3px;
    transition: background 0.3s;
}

.btn-edit {
    background: #ffc107;
    color: #212529;
}

.btn-edit:hover {
    background: #e0a800;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
}
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Payment History</h1>
            <a href="dashboard.php" class="back-btn">
                <ion-icon name="arrow-back"></ion-icon>
                Back to Dashboard
            </a>
        </div>
        
        <div class="stats-cards">
            <div class="stat-card">
                <h3>Total Payments</h3>
                <div class="amount">NRS <?php 
                    $total = array_sum(array_column($payments, 'amount'));
                    echo number_format($total, 2);
                ?></div>
            </div>
            
            <div class="stat-card">
                <h3>Completed Payments</h3>
                <div class="amount">
                    <?php 
                        $completed = array_filter($payments, fn($p) => $p['status'] == 'completed');
                        echo count($completed);
                    ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Pending Payments</h3>
                <div class="amount">
                    <?php 
                        $pending = array_filter($payments, fn($p) => $p['status'] == 'pending');
                        echo count($pending);
                    ?>
                </div>
            </div>
        </div>
        
<div class="payments-table">
    <?php if (empty($payments)): ?>
        <div class="no-data">
            <h3>No payment history found</h3>
            <p>You haven't made any payments yet.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Booking</th>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Transaction ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($payments as $payment): ?>
                <tr>
                    <td>#<?php echo $payment['id']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($payment['room_type'] . ' - Room ' . $payment['room_number']); ?><br>
                        <small><?php echo date('d M Y', strtotime($payment['check_in'])); ?> to <?php echo date('d M Y', strtotime($payment['check_out'])); ?></small>
                    </td>
                    <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                    <td>
                        <span class="method-badge">
                            <?php echo ucfirst($payment['payment_method']); ?>
                        </span>
                    </td>
                    <td><?php echo $payment['transaction_id'] ?: 'N/A'; ?></td>
                    <td>NRS <?php echo number_format($payment['amount'], 2); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $payment['status']; ?>">
                            <?php echo ucfirst($payment['status']); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($payment['status'] == 'pending'): ?>
                            <a href="make-payment.php?payment_id=<?php echo $payment['id']; ?>" 
                               class="btn-success" style="padding: 5px 10px; font-size: 12px; display: inline-block; margin-top: 5px;">
                                Edit
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>