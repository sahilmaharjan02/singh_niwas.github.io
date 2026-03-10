<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$booking_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$booking_id || $booking_id < 1) {
    $_SESSION['error'] = "Invalid booking ID.";
    header('Location: admin-bookings.php');
    exit();
}

try {
    $sql = "SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone, 
                   u.address as user_address, r.room_number, r.room_type, r.price_per_night,
                   r.description as room_description, r.amenities
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN rooms r ON b.room_id = r.id 
            WHERE b.id = ?";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        $_SESSION['error'] = "Booking not found.";
        header('Location: admin-bookings.php');
        exit();
    }

    $check_in = new DateTime($booking['check_in']);
    $check_out = new DateTime($booking['check_out']);
    $nights = $check_in->diff($check_out)->days;
    
    if ($nights < 1) {
        $nights = 1; 
    }
    
} catch (PDOException $e) {
    error_log("Database error in booking-details.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while fetching booking details.";
    header('Location: admin-bookings.php');
    exit();
} catch (Exception $e) {
    error_log("Error in booking-details.php: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing booking details.";
    header('Location: admin-bookings.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - Singh Niwas</title>
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
            color: #333;
        }

        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: margin-left 0.3s;
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
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: var(--text-dark);
            font-size: 24px;
            margin: 0;
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
            align-items: flex-start;
        }

        .detail-label {
            flex: 0 0 200px;
            font-weight: 600;
            color: var(--text-dark);
            min-width: 200px;
        }

        .detail-value {
            flex: 1;
            word-break: break-word;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            flex-wrap: wrap;
            gap: 15px;
        }

        .booking-id {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: bold;
        }

        .status-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            text-transform: uppercase;
        }

        .status-pending { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7;
        }
        .status-confirmed { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .status-cancelled { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .status-completed { 
            background: #cce5ff; 
            color: #004085; 
            border: 1px solid #b8daff;
        }
        
        .payment-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background: #e9ecef;
            color: #495057;
            border: 1px solid #dee2e6;
            text-transform: uppercase;
        }

        .price-breakdown {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #dee2e6;
        }

        .price-item:last-child {
            border-bottom: none;
        }

        .total-price {
            font-size: 18px;
            font-weight: bold;
            color: var(--success);
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
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

        .btn-group {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        h3 {
            color: var(--text-dark);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary-color);
            font-size: 18px;
        }

        .alert {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .special-request {
            font-style: italic;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid var(--primary-color);
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label {
                flex: 1;
                margin-bottom: 5px;
                min-width: unset;
            }
            
            .booking-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn {
                justify-content: center;
            }
        }

        @media print {
            .sidebar, .header, .btn-group, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            
            .detail-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                padding: 20px !important;
                margin: 0 !important;
            }
            
            body {
                background: white !important;
                font-size: 12pt !important;
            }
            
            h3 {
                color: black !important;
                border-bottom: 2px solid black !important;
            }
            
            .status-badge, .payment-badge {
                background: none !important;
                color: black !important;
                border: 1px solid black !important;
            }
            
            .price-breakdown {
                background: none !important;
                border: 1px solid #ddd !important;
            }
            
            @page {
                margin: 0.5in;
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
            <h1>Booking Details</h1>
            <div>
                <a href="admin-bookings.php" class="btn btn-secondary">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Bookings
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <div class="detail-card">
            <div class="booking-header">
                <div class="booking-id">Booking #<?php echo htmlspecialchars($booking['id']); ?></div>
                <div class="status-container">
                    <span class="status-badge status-<?php echo htmlspecialchars($booking['status']); ?>">
                        <?php echo htmlspecialchars(strtoupper($booking['status'])); ?>
                    </span>
                    <span class="payment-badge">
                        <?php echo htmlspecialchars(strtoupper($booking['payment_status'])); ?>
                    </span>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Booking Date:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars(date('F d, Y H:i', strtotime($booking['booking_date']))); ?>
                </div>
            </div>

            <h3>Customer Information</h3>
            <div class="detail-row">
                <div class="detail-label">Customer Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['user_name']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Email:</div>
                <div class="detail-value">
                    <a href="mailto:<?php echo htmlspecialchars($booking['user_email']); ?>">
                        <?php echo htmlspecialchars($booking['user_email']); ?>
                    </a>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Phone:</div>
                <div class="detail-value">
                    <?php echo !empty($booking['user_phone']) ? htmlspecialchars($booking['user_phone']) : 'N/A'; ?>
                </div>
            </div>
            
            <?php if (!empty($booking['user_address'])): ?>
            <div class="detail-row">
                <div class="detail-label">Address:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['user_address']); ?></div>
            </div>
            <?php endif; ?>

            <h3>Room Information</h3>
            <div class="detail-row">
                <div class="detail-label">Room Type:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['room_type']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Room Number:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['room_number']); ?></div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Room Price/Night:</div>
                <div class="detail-value">NRS <?php echo number_format($booking['price_per_night'], 2); ?></div>
            </div>
            
            <?php if (!empty($booking['room_description'])): ?>
            <div class="detail-row">
                <div class="detail-label">Description:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['room_description']); ?></div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($booking['amenities'])): ?>
            <div class="detail-row">
                <div class="detail-label">Amenities:</div>
                <div class="detail-value"><?php echo htmlspecialchars($booking['amenities']); ?></div>
            </div>
            <?php endif; ?>

            <h3>Booking Dates</h3>
            <div class="detail-row">
                <div class="detail-label">Check-in Date:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars(date('l, F d, Y', strtotime($booking['check_in']))); ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Check-out Date:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars(date('l, F d, Y', strtotime($booking['check_out']))); ?>
                </div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Duration:</div>
                <div class="detail-value">
                    <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                </div>
            </div>

            <?php if (!empty($booking['special_requests'])): ?>
            <h3>Special Requests</h3>
            <div class="special-request">
                "<?php echo htmlspecialchars($booking['special_requests']); ?>"
            </div>
            <?php endif; ?>

            <div class="price-breakdown">
                <h3 style="border: none; margin-top: 0; padding-top: 0; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">Price Breakdown</h3>
                <div class="price-item">
                    <span>Room Price/Night:</span>
                    <span>NRS <?php echo number_format($booking['price_per_night'], 2); ?></span>
                </div>
                <div class="price-item">
                    <span>Number of Nights:</span>
                    <span><?php echo $nights; ?></span>
                </div>
                <?php if (isset($booking['discount_amount']) && $booking['discount_amount'] > 0): ?>
                <div class="price-item">
                    <span>Discount:</span>
                    <span>- NRS <?php echo number_format($booking['discount_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($booking['tax_amount']) && $booking['tax_amount'] > 0): ?>
                <div class="price-item">
                    <span>Tax:</span>
                    <span>+ NRS <?php echo number_format($booking['tax_amount'], 2); ?></span>
                </div>
                <?php endif; ?>
                <div class="price-item total-price">
                    <span>Total Amount:</span>
                    <span>NRS <?php echo number_format($booking['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>

        <div class="btn-group">
            <button class="btn btn-primary" onclick="location.href='admin-edit-booking.php?id=<?php echo $booking['id']; ?>'">
                <ion-icon name="create-outline"></ion-icon> Update Booking
            </button>
            <button class="btn btn-success" onclick="window.print()">
                <ion-icon name="print-outline"></ion-icon> Print Invoice
            </button>
            <?php if ($booking['status'] != 'cancelled'): ?>
            <button class="btn btn-warning" onclick="cancelBooking(<?php echo $booking['id']; ?>)">
                <ion-icon name="close-circle-outline"></ion-icon> Cancel Booking
            </button>
            <?php endif; ?>
            <button class="btn btn-danger" onclick="deleteBooking(<?php echo $booking['id']; ?>)">
                <ion-icon name="trash-outline"></ion-icon> Delete Booking
            </button>
        </div>
    </div>

    <script>
        function deleteBooking(id) {
            if (confirm('Are you sure you want to permanently delete this booking? This action cannot be undone.')) {
                window.location.href = 'admin-delete-booking.php?id=' + id;
            }
        }
        
        function cancelBooking(id) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = 'admin-edit-booking.php?id=' + id;
            }
        }
        

        window.addEventListener('beforeunload', function (e) {
        });
    </script>
</body>
</html>