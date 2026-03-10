<?php
session_start();
require_once '../db_connection.php';
require_once 'session_helper.php';

requireAdminLogin();

$error = '';
$success = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Security token invalid. Please try again.';
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $room_number = trim($_POST['room_number']);
        $room_type = trim($_POST['room_type']);
        $description = trim($_POST['description']);
        $price_per_night = (float)$_POST['price_per_night'];
        $capacity = (int)$_POST['capacity'];
        $status = $_POST['status'];
        
        error_log("Inserting: $room_number, $room_type, $description, $price_per_night, $capacity, $status");
        
        if (empty($room_number) || empty($room_type) || $price_per_night <= 0 || $capacity <= 0) {
            $error = 'Please fill all required fields with valid values';
        } else {
            
            $check_stmt = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
            $check_stmt->execute([$room_number]);
            if ($check_stmt->fetch()) {
                $error = 'Room number already exists';
            } else {
                try {
                    $insert_stmt = $pdo->prepare("INSERT INTO rooms (room_number, room_type, description, price_per_night, capacity, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $insert_stmt->execute([$room_number, $room_type, $description, $price_per_night, $capacity, $status]);
                    
                    $success = 'Room added successfully!';
                    $_POST = []; 
                } catch (Exception $e) {
                    $error = 'Failed to add room: ' . $e->getMessage();
                    
                    error_log("Insert error: " . $e->getMessage());
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room - Singh Niwas</title>
    <style>
        .required:after {
            content: " ";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Add New Room</h1>
            <div>
                <a href="admin-rooms.php" class="btn btn-warning">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    Back to Rooms
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <ion-icon name="alert-circle-outline"></ion-icon>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <ion-icon name="checkmark-circle-outline"></ion-icon>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div class="form-group">
                    <label class="required">Room Number *</label>
                    <input type="text" name="room_number" value="<?php echo htmlspecialchars($_POST['room_number'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="required">Room Type *</label>
                    <select name="room_type" required>
                        <option value="">Select Type</option>
                        <option value="Standard Room" <?php echo ($_POST['room_type'] ?? '') == 'Standard Room' ? 'selected' : ''; ?>>Standard Room</option>
                        <option value="Deluxe Room" <?php echo ($_POST['room_type'] ?? '') == 'Deluxe Room' ? 'selected' : ''; ?>>Deluxe Room</option>
                        <option value="Executive Suite" <?php echo ($_POST['room_type'] ?? '') == 'Executive Suite' ? 'selected' : ''; ?>>Executive Suite</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="required">Price Per Night (NRS) *</label>
                    <input type="number" name="price_per_night" step="0.01" min="0" 
                           value="<?php echo htmlspecialchars($_POST['price_per_night'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="required">Capacity (persons) *</label>
                    <input type="number" name="capacity" min="1" max="10" 
                           value="<?php echo htmlspecialchars($_POST['capacity'] ?? '2'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="available" selected>Available</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <ion-icon name="add-circle-outline"></ion-icon>
                        Add Room
                    </button>
                    <a href="admin-rooms.php" class="btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>