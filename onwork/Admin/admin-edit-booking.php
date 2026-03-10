<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$booking_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    header('Location: admin-bookings.php');
    exit();
}

$users = $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll();
$rooms = $pdo->query("SELECT id, room_number, room_type, price_per_night FROM rooms WHERE status = 'available' OR id = {$booking['room_id']} ORDER BY room_number")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $room_id = (int)$_POST['room_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $status = $_POST['status'];
    $payment_status = $_POST['payment_status'];
    $total_amount = (float)$_POST['total_amount'];
    $special_requests = trim($_POST['special_requests']);
    
    if ($check_in >= $check_out) {
        $error = 'Check-out date must be after check-in date';
    } else {
        try {
            $update_stmt = $pdo->prepare("UPDATE bookings SET user_id = ?, room_id = ?, check_in = ?, check_out = ?, status = ?, payment_status = ?, total_amount = ?, special_requests = ? WHERE id = ?");
            $update_stmt->execute([$user_id, $room_id, $check_in, $check_out, $status, $payment_status, $total_amount, $special_requests, $booking_id]);
            
            $success = 'Booking updated successfully!';

            $stmt->execute([$booking_id]);
            $booking = $stmt->fetch();
        } catch (Exception $e) {
            $error = 'Update failed: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - Singh Niwas</title>
    <style>
        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
</head>
<body>
  
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="header">
            <h1>Edit Booking #<?php echo $booking['id']; ?></h1>
            <!-- <div>
                <a href="admin-bookdetail.php?id=<?php echo $booking_id; ?>" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Details
                </a>
            </div> -->
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="user_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                <?php echo $booking['user_id'] == $user['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Room *</label>
                    <select name="room_id" required id="room_select" onchange="updatePrice()">
                        <option value="">Select Room</option>
                        <?php foreach($rooms as $room): ?>
                            <option value="<?php echo $room['id']; ?>" 
                                data-price="<?php echo $room['price_per_night']; ?>"
                                <?php echo $booking['room_id'] == $room['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($room['room_type']); ?> - Room #<?php echo htmlspecialchars($room['room_number']); ?>
                                (NRS <?php echo number_format($room['price_per_night'], 2); ?>/night)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Booking Dates *</label>
                    <div class="date-inputs">
                        <div>
                            <label>Check-in Date</label>
                            <input type="date" name="check_in" id="check_in" 
                                   value="<?php echo $booking['check_in']; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div>
                            <label>Check-out Date</label>
                            <input type="date" name="check_out" id="check_out" 
                                   value="<?php echo $booking['check_out']; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Total Amount (NRS) *</label>
                    <input type="number" name="total_amount" id="total_amount" 
                           step="0.01" min="0" value="<?php echo $booking['total_amount']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Booking Status</label>
                    <select name="status">
                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Payment Status</label>
                    <select name="payment_status">
                        <option value="unpaid" <?php echo $booking['payment_status'] == 'unpaid' ? 'selected' : ''; ?>>Unpaid</option>
                        <option value="paid" <?php echo $booking['payment_status'] == 'paid' ? 'selected' : ''; ?>>Paid</option>
                        <option value="refunded" <?php echo $booking['payment_status'] == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests"><?php echo htmlspecialchars($booking['special_requests'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <ion-icon name="save-outline"></ion-icon>
                        Update Booking
                    </button>
                    <a href="admin-bookings.php?id=<?php echo $booking_id; ?>" class="btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updatePrice() {
            const roomSelect = document.getElementById('room_select');
            const checkIn = document.getElementById('check_in');
            const checkOut = document.getElementById('check_out');
            const totalAmount = document.getElementById('total_amount');
            
            const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
            const pricePerNight = selectedRoom.getAttribute('data-price');
            
            if (checkIn.value && checkOut.value && pricePerNight) {
                const checkInDate = new Date(checkIn.value);
                const checkOutDate = new Date(checkOut.value);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    totalAmount.value = (pricePerNight * nights).toFixed(2);
                }
            }
        }
        
        document.getElementById('room_select').addEventListener('change', updatePrice);
        document.getElementById('check_in').addEventListener('change', updatePrice);
        document.getElementById('check_out').addEventListener('change', updatePrice);
        
        document.getElementById('check_in').addEventListener('change', function() {
            document.getElementById('check_out').min = this.value;
            updatePrice();
        });
    </script>
</body>
</html>