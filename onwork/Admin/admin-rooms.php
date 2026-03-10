<?php
session_start();
require_once '../db_connection.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = 'WHERE 1=1';
$params = [];

if (!empty($search)) {
    $where .= " AND (room_number LIKE ? OR room_type LIKE ? OR description LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($type_filter)) {
    $where .= " AND room_type = ?";
    $params[] = $type_filter;
}

if (!empty($status_filter)) {
    $where .= " AND status = ?";
    $params[] = $status_filter;
}

$count_sql = "SELECT COUNT(*) FROM rooms $where";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_rooms = $count_stmt->fetchColumn();
$total_pages = ceil($total_rooms / $limit);

$sql = "SELECT * FROM rooms $where ORDER BY room_number ASC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$type_stmt = $pdo->query("SELECT DISTINCT room_type FROM rooms ORDER BY room_type");
$room_types = $type_stmt->fetchAll();


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
       
        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ?");
        $check_stmt->execute([$id]);
        $booking_count = $check_stmt->fetchColumn();
        
        if ($booking_count > 0) {
            $_SESSION['error'] = "Cannot delete room with existing bookings. Delete bookings first.";
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
            if ($delete_stmt->execute([$id])) {
                $_SESSION['success'] = "Room deleted successfully!";
            } else {
                $_SESSION['error'] = "Failed to delete room.";
            }
        }
        header('Location: admin-rooms.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Rooms - Singh Niwas</title>
    <style>
        .filter-bar {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-available { background: #d4edda; color: #155724; }
        .status-occupied { background: #f8d7da; color: #721c24; }
        .status-maintenance { background: #fff3cd; color: #856404; }

        .room-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <div class="header">
            <h1>Manage Rooms</h1>
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

        <div class="action-bar">
            <form method="GET" class="search-box">
                <ion-icon name="search-outline"></ion-icon>
                <input type="text" name="search" placeholder="Search rooms..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </form>
            
            <div class="filter-bar">
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach($room_types as $type): ?>
                        <option value="<?php echo $type['room_type']; ?>" 
                            <?php echo $type_filter == $type['room_type'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['room_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="available" <?php echo $status_filter == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="occupied" <?php echo $status_filter == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                    <option value="maintenance" <?php echo $status_filter == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                </select>
                
                <?php if (!empty($search)): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
            </div>
            
            <button class="btn btn-primary" onclick="location.href='admin-add-room.php'">
                <ion-icon name="add-circle-outline"></ion-icon>
                Add New Room
            </button>
        </div>

        <div class="dashboard-section">
            <h3 style="margin-bottom: 20px; color: var(--text-dark);">
                Rooms (<?php echo $total_rooms; ?>)
            </h3>
            
            <?php if (empty($rooms)): ?>
                <div class="empty-state">
                    <ion-icon name="bed-outline"></ion-icon>
                    <h3>No rooms found</h3>
                    <p><?php echo empty($search) ? 'No rooms in the system yet.' : 'No rooms match your search.'; ?></p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Room No</th>
                            <th>Type</th>
                            <th>Price/Night</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($rooms as $room): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                            <td>NRS <?php echo number_format($room['price_per_night'], 2); ?></td>
                            <td><?php echo $room['capacity']; ?> persons</td>
                            <td>
                                <span class="status-badge status-<?php echo $room['status']; ?>">
                                    <?php echo ucfirst($room['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-sm btn-danger" 
                                            onclick="deleteRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['room_number']); ?>')">
                                        <ion-icon name="trash-outline"></ion-icon> Delete
                                    </button>
                                    <button class="btn-sm btn-success" 
                                            onclick="location.href='admin-room-detail.php?id=<?php echo $room['id']; ?>'">
                                        <ion-icon name="eye-outline"></ion-icon> View
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
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
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
        function deleteRoom(id, roomNumber) {
            if (confirm(`Are you sure you want to delete room "${roomNumber}"? This action cannot be undone.`)) {
                window.location.href = 'admin-rooms.php?delete=' + id;
            }
        }
    </script>
</body>
</html>