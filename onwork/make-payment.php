<?php
session_start();
require_once 'db_connection.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$edit_mode = false;
$payment = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = $_POST['transaction_id'] ?? null;
    $payment_id = $_POST['payment_id'] ?? null; // For edit mode
    
    try {
        // Get booking details
        $stmt = $pdo->prepare("
            SELECT b.*, r.room_type, r.room_number 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            WHERE b.id = ? AND b.user_id = ? AND b.status != 'cancelled'
        ");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            $_SESSION['payment_error'] = 'Booking not found or already cancelled';
            header('Location: dashboard.php');
            exit();
        }
        
        $pdo->beginTransaction();
        
        if ($payment_id) {
            // UPDATE existing payment (edit mode)
            $stmt = $pdo->prepare("
                UPDATE payments 
                SET payment_method = ?, transaction_id = ?, status = 'pending'
                WHERE id = ? AND user_id = ? AND booking_id = ?
            ");
            $stmt->execute([$payment_method, $transaction_id, $payment_id, $user_id, $booking_id]);
            
            $action_message = 'Payment updated successfully! Please wait for admin confirmation.';
        } else {
            // CREATE new payment
            $stmt = $pdo->prepare("
                INSERT INTO payments (booking_id, user_id, amount, payment_method, transaction_id, status) 
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$booking_id, $user_id, $booking['total_amount'], $payment_method, $transaction_id]);
            
            // Update booking payment status
            $stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'paid' WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            // Update booking status to confirmed
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
            $stmt->execute([$booking_id]);
            
            // Update room status to occupied
            $stmt = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ?");
            $stmt->execute([$booking['room_id']]);
            
            $action_message = 'Payment submitted successfully! Please wait for admin confirmation.';
        }
        
        $pdo->commit();
        
        $_SESSION['payment_success'] = $action_message;
        header('Location: dashboard.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['payment_error'] = 'Payment failed: ' . $e->getMessage();
        header('Location: dashboard.php');
        exit();
    }
} else {
    $booking_id = $_GET['booking_id'] ?? null;
    $payment_id = $_GET['payment_id'] ?? null;
    
    // Edit mode - if payment_id is provided
    if ($payment_id) {
        $edit_mode = true;
        
        // Get payment details for editing
        $stmt = $pdo->prepare("
            SELECT p.*, b.*, r.room_type, r.room_number 
            FROM payments p 
            JOIN bookings b ON p.booking_id = b.id 
            JOIN rooms r ON b.room_id = r.id 
            WHERE p.id = ? AND p.user_id = ? AND p.status = 'pending'
        ");
        $stmt->execute([$payment_id, $user_id]);
        $payment = $stmt->fetch();
        
        if (!$payment) {
            $_SESSION['payment_error'] = 'Payment not found or cannot be edited';
            header('Location: dashboard.php');
            exit();
        }
        
        $booking_id = $payment['booking_id'];
    } 
    // New payment mode - if booking_id is provided
    elseif ($booking_id) {
        // Get booking details for new payment
        $stmt = $pdo->prepare("
            SELECT b.*, r.room_type, r.room_number 
            FROM bookings b 
            JOIN rooms r ON b.room_id = r.id 
            WHERE b.id = ? AND b.user_id = ? AND b.status = 'pending'
        ");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();
        
        if (!$booking) {
            $_SESSION['payment_error'] = 'Booking not found or already processed';
            header('Location: dashboard.php');
            exit();
        }
    } else {
        header('Location: dashboard.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit Payment' : 'Make Payment'; ?> - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #e6f2ff;
            --text-dark: #002960;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)),
                        url('https://source.unsplash.com/1920x1080/?payment') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .payment-container {
            width: 100%;
            max-width: 500px;
        }
        
        .payment-card {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .payment-header h1 {
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        
        .payment-header p {
            color: #666;
        }
        
        .payment-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .booking-info {
            background: var(--background-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .booking-info h3 {
            margin-bottom: 10px;
            color: var(--text-dark);
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .info-label {
            color: #666;
        }
        
        .info-value {
            color: var(--text-dark);
            font-weight: 500;
        }
        
        .total-amount {
            background: var(--primary-color);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 25px;
            font-size: 20px;
            font-weight: 600;
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
        
        .form-group select,
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .method-option {
            border: 2px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .method-option:hover {
            border-color: var(--primary-color);
        }
        
        .method-option.selected {
            border-color: var(--primary-color);
            background: rgba(24, 145, 209, 0.1);
        }
        
        .method-icon {
            font-size: 24px;
            margin-bottom: 5px;
            color: var(--primary-color);
        }
        
        .payment-button {
            width: 100%;
            padding: 15px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.3s;
        }
        
        .payment-button:hover {
            background: var(--primary-dark);
        }
        
        .delete-button {
            width: 100%;
            padding: 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: background 0.3s;
            margin-top: 10px;
        }
        
        .delete-button:hover {
            background: #c82333;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary-color);
            text-decoration: none;
            margin-top: 15px;
        }
        
        @media (max-width: 480px) {
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .payment-card {
                padding: 20px;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
</head>
<body>
    <div class="payment-container">
        <a href="dashboard.php" class="back-link" style="color: white; margin-bottom: 20px; display: inline-block;">
            <ion-icon name="arrow-back"></ion-icon> Back to Dashboard
        </a>
        
        <div class="payment-card">
            <div class="payment-header">
                <h1><?php echo $edit_mode ? 'Edit Payment' : 'Complete Payment'; ?></h1>
                <p><?php echo $edit_mode ? 'Update your payment details' : 'Complete your booking by making payment'; ?></p>
                
                <?php if ($edit_mode && $payment['status'] == 'pending'): ?>
                    <div class="payment-status status-pending">
                        Status: Pending
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (isset($_SESSION['payment_error'])): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($_SESSION['payment_error']); ?>
                    <?php unset($_SESSION['payment_error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="booking-info">
                <h3>Booking Details</h3>
                <div class="info-row">
                    <span class="info-label">Booking ID:</span>
                    <span class="info-value">#<?php echo $edit_mode ? $payment['booking_id'] : $booking['id']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Room:</span>
                    <span class="info-value"><?php echo htmlspecialchars(($edit_mode ? $payment['room_type'] : $booking['room_type']) . ' - ' . ($edit_mode ? $payment['room_number'] : $booking['room_number'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-in:</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($edit_mode ? $payment['check_in'] : $booking['check_in'])); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Check-out:</span>
                    <span class="info-value"><?php echo date('d M Y', strtotime($edit_mode ? $payment['check_out'] : $booking['check_out'])); ?></span>
                </div>
            </div>
            
            <div class="total-amount">
                Total Amount: NRS <?php echo number_format($edit_mode ? $payment['amount'] : $booking['total_amount'], 2); ?>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="booking_id" value="<?php echo $edit_mode ? $payment['booking_id'] : $booking['id']; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="payment_id" value="<?php echo $payment['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-methods">
                        <div class="method-option" onclick="selectMethod('esewa')" data-method="esewa">
                            <div class="method-icon">💰</div>
                            <div>eSewa</div>
                        </div>
                        <!-- <div class="method-option" onclick="selectMethod('khalti')" data-method="khalti">
                            <div class="method-icon">💳</div>
                            <div>Khalti</div>
                        </div> -->
                        <div class="method-option" onclick="selectMethod('cash')" data-method="cash">
                            <div class="method-icon">💵</div>
                            <div>Cash</div>
                        </div>
                        <div class="method-option" onclick="selectMethod('bank')" data-method="bank">
                            <div class="method-icon">🏦</div>
                            <div>Bank Transfer</div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="payment_method" required>
                </div>
                
                <div class="form-group" id="transaction_field">
                    <label for="transaction_id">Transaction ID/Reference Number</label>
                    <input type="text" id="transaction_id" name="transaction_id" 
                           placeholder="Enter transaction ID or reference number"
                           value="<?php echo $edit_mode ? htmlspecialchars($payment['transaction_id'] ?? '') : ''; ?>">
                </div>
                
                <button type="submit" class="payment-button">
                    <ion-icon name="checkmark-circle"></ion-icon>
                    <?php echo $edit_mode ? 'Update Payment' : 'Submit Payment'; ?>
                </button>
                
                <?php if ($edit_mode && $payment['status'] == 'pending'): ?>
                    <button type="button" onclick="deletePayment(<?php echo $payment['id']; ?>)" class="delete-button">
                        <ion-icon name="trash-outline"></ion-icon>
                        Delete Payment
                    </button>
                <?php endif; ?>
            </form>
            
            <p style="text-align: center; margin-top: 15px; color: #666; font-size: 12px;">
                <?php echo $edit_mode ? 
                    'Payment update will require admin verification.' : 
                    'Your booking will be confirmed after admin verifies the payment.'; ?>
            </p>
        </div>
    </div>
    
    <script>
        function selectMethod(method) {
            // Remove selected class from all
            document.querySelectorAll('.method-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked
            event.target.closest('.method-option').classList.add('selected');
            
            // Set hidden input value
            document.getElementById('payment_method').value = method;
            
            // Show transaction field for non-cash methods
            const transactionField = document.getElementById('transaction_field');
            if (method === 'cash') {
                transactionField.style.display = 'none';
                document.getElementById('transaction_id').required = false;
            } else {
                transactionField.style.display = 'block';
                document.getElementById('transaction_id').required = true;
            }
        }
        
        function deletePayment(paymentId) {
            if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
                fetch('delete-payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'payment_id=' + paymentId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payment deleted successfully');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }
        
        // Set default selection based on existing payment (for edit mode)
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($edit_mode && isset($payment['payment_method'])): ?>
                // Select the current payment method
                selectMethod('<?php echo $payment["payment_method"]; ?>');
                // Trigger click on the correct method option
                document.querySelector(`.method-option[data-method="<?php echo $payment['payment_method']; ?>"]`).click();
            <?php else: ?>
                // Default for new payments
                selectMethod('esewa');
            <?php endif; ?>
        });
    </script>
</body>
</html>