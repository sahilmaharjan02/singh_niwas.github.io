<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$error = '';
$success = '';

$users = $pdo->query("SELECT id, name, email FROM users ORDER BY name")->fetchAll();
$rooms = $pdo->query("SELECT id, room_number, room_type, price_per_night FROM rooms WHERE status = 'available' ORDER BY room_number")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security token invalid. Please try again.';
    } else {
        $user_id = (int)$_POST['user_id'];
        $room_id = (int)$_POST['room_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $special_requests = trim($_POST['special_requests']);
        
        $today = date('Y-m-d');
        if ($check_in < $today) {
            $error = 'Check-in date cannot be in the past';
        } elseif ($check_in >= $check_out) {
            $error = 'Check-out date must be after check-in date';
        } else {
            $room_stmt = $pdo->prepare("SELECT price_per_night FROM rooms WHERE id = ?");
            $room_stmt->execute([$room_id]);
            $room_price = $room_stmt->fetchColumn();
            
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            $nights = $check_in_date->diff($check_out_date)->days;
            $total_amount = $room_price * $nights;
            
            $availability_stmt = $pdo->prepare("
                SELECT COUNT(*) FROM bookings 
                WHERE room_id = ? 
                AND status NOT IN ('cancelled', 'completed')
                AND ((check_in <= ? AND check_out >= ?) OR (check_in <= ? AND check_out >= ?))
            ");
            $availability_stmt->execute([$room_id, $check_out, $check_in, $check_in, $check_out]);
            
            if ($availability_stmt->fetchColumn() > 0) {
                $error = 'Room is not available for the selected dates';
            } else {
                try {
                    $insert_stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, special_requests, status, payment_status, booking_date) VALUES (?, ?, ?, ?, ?, ?, 'confirmed', 'unpaid', NOW())");
                    $insert_stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount, $special_requests]);
                    
                    $update_room = $pdo->prepare("UPDATE rooms SET status = 'occupied' WHERE id = ?");
                    $update_room->execute([$room_id]);
                    
                    $success = 'Booking created successfully! Total: NRS ' . number_format($total_amount, 2);
                    
                    $_POST = [];
                } catch (Exception $e) {
                    $error = 'Failed to create booking: ' . $e->getMessage();
                }
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Booking - Singh Niwas</title>
    <style>
        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .price-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Add New Booking</h1>
            <div>
                <a href="admin-bookings.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Bookings
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label>Customer *</label>
                    <select name="user_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                <?php echo ($_POST['user_id'] ?? '') == $user['id'] ? 'selected' : ''; ?>>
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
                                <?php echo ($_POST['room_id'] ?? '') == $room['id'] ? 'selected' : ''; ?>>
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
                                   value="<?php echo $_POST['check_in'] ?? date('Y-m-d'); ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div>
                            <label>Check-out Date</label>
                            <input type="date" name="check_out" id="check_out" 
                                   value="<?php echo $_POST['check_out'] ?? date('Y-m-d', strtotime('+1 day')); ?>" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>
                </div>
                
                <div class="price-preview" id="price_preview">
                    <div id="price_details"></div>
                </div>
                
                <div class="form-group">
                    <label>Special Requests</label>
                    <textarea name="special_requests"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <ion-icon name="calendar-outline"></ion-icon>
                        Create Booking
                    </button>
                    <a href="admin-bookings.php" class="btn-secondary">
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
            const pricePreview = document.getElementById('price_preview');
            const priceDetails = document.getElementById('price_details');
            
            const selectedRoom = roomSelect.options[roomSelect.selectedIndex];
            const pricePerNight = selectedRoom.getAttribute('data-price');
            
            if (checkIn.value && checkOut.value && pricePerNight) {
                const checkInDate = new Date(checkIn.value);
                const checkOutDate = new Date(checkOut.value);
                const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
                
                if (nights > 0) {
                    const total = (pricePerNight * nights).toFixed(2);
                    priceDetails.innerHTML = `
                        <strong>Price Breakdown:</strong><br>
                        Room Price/Night: NRS ${parseFloat(pricePerNight).toFixed(2)}<br>
                        Number of Nights: ${nights}<br>
                        <strong>Total Amount: NRS ${total}</strong>
                    `;
                    pricePreview.style.display = 'block';
                } else {
                    pricePreview.style.display = 'none';
                }
            } else {
                pricePreview.style.display = 'none';
            }
        }
        
        document.getElementById('room_select').addEventListener('change', updatePrice);
        document.getElementById('check_in').addEventListener('change', function() {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            document.getElementById('check_out').min = nextDay.toISOString().split('T')[0];
            updatePrice();
        });
        document.getElementById('check_out').addEventListener('change', updatePrice);
        
        window.onload = updatePrice;
    </script>
</body>
</html>