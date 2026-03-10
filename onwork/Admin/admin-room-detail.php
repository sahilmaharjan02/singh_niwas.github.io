<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$room_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: admin-rooms.php');
    exit();
}

$bookings_sql = "SELECT b.*, u.name as customer_name 
                 FROM bookings b 
                 JOIN users u ON b.user_id = u.id 
                 WHERE b.room_id = ? AND b.status IN ('pending', 'confirmed') 
                 ORDER BY b.check_in";
$bookings_stmt = $pdo->prepare($bookings_sql);
$bookings_stmt->execute([$room_id]);
$bookings = $bookings_stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details - Singh Niwas</title>
</head>
<body>
   
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Room Details: <?php echo htmlspecialchars($room['room_number']); ?></h1>
            <div>
                <a href="admin-rooms.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Rooms
                </a>
            </div>
        </div>

        <div class="detail-card">
            <div class="booking-header">
                <div class="booking-id">Room #<?php echo $room['room_number']; ?></div>
                <div>
                    <span class="status-badge status-<?php echo $room['status']; ?>">
                        <?php echo strtoupper($room['status']); ?>
                    </span>
                </div>
            </div>

            <?php if (!empty($room['image_url'])): ?>
            <div style="text-align: center; margin: 20px 0;">
                <img src="<?php echo htmlspecialchars($room['image_url']); ?>" 
                     alt="Room <?php echo $room['room_number']; ?>" 
                     style="max-width: 100%; max-height: 400px; border-radius: 10px;">
            </div>
            <?php endif; ?>

            <div class="detail-row">
                <div class="detail-label">Room Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($room['room_type']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Price Per Night:</div>
                <div class="detail-value">NRS <?php echo number_format($room['price_per_night'], 2); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Capacity:</div>
                <div class="detail-value"><?php echo $room['capacity']; ?> persons</div>
            </div>
            
            
            <div class="detail-row">
                <div class="detail-label">Description:</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($room['description'])); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Created Date:</div>
                <div class="detail-value"><?php echo date('F d, Y'); ?></div>
            </div>
        </div>

        <?php if (!empty($bookings)): ?>
        <div class="dashboard-section" style="margin-top: 30px;">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">Current Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $booking['status']; ?>">
                                <?php echo ucfirst($booking['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 30px;">
            <button class="btn btn-primary" onclick="location.href='admin-edit-room.php?id=<?php echo $room_id; ?>'">
                <ion-icon name="create-outline"></ion-icon> Edit Room
            </button>
            <button class="btn btn-success" onclick="window.print()">
                <ion-icon name="print-outline"></ion-icon> Print Details
            </button>
            <button class="btn btn-danger" onclick="deleteRoom(<?php echo $room_id; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')">
                <ion-icon name="trash-outline"></ion-icon> Delete Room
            </button>
        </div>
    </div>

    <script>
        function deleteRoom(id, roomNumber) {
            if (confirm(`Are you sure you want to delete room "${roomNumber}"? This action cannot be undone.`)) {
                window.location.href = 'admin-rooms.php?delete=' + id;
            }
        }
    </script>
</body>
</html>