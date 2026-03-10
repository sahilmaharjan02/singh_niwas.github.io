<?php
require_once '../db_connection.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $email = trim($_POST['email']);
    $full_name = trim($_POST['full_name']);
    
    if (empty($username) || empty($password) || empty($email) || empty($full_name)) {
        $error = 'All fields are required';
    } else {
       
        $create_table_sql = "CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            full_name VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        try {
            $pdo->exec($create_table_sql);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin
            $stmt = $pdo->prepare("INSERT INTO admins (username, password, email, full_name) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashed_password, $email, $full_name]);
            
            $success = 'Admin user created successfully! You can now login.';
            $success .= '<br><strong>Username:</strong> ' . $username;
            $success .= '<br><strong>Email:</strong> ' . $email;
            $success .= '<br><strong>Password:</strong> ' . $password;
            $success .= '<br><br><strong style="color: red;">⚠️ IMPORTANT: Delete this file after setup!</strong>';
            
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Singh Niwas</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #1891d1, #1d64c2);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        
        .setup-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        
        h1 {
            color: #002960;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #002960;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: #1891d1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #1d64c2;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1>Admin Setup</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This file should be deleted after setup. 
            It creates the admin table and adds the first admin user.
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" required placeholder="Enter full name">
                </div>
                
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" required placeholder="Choose username">
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter email address">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Choose password">
                </div>
                
                <button type="submit" class="btn">Create Admin Account</button>
            </form>
        <?php endif; ?>
        
        <div style="margin-top: 20px; text-align: center; color: #666; font-size: 12px;">
            Singh Niwas Admin Panel Setup
        </div>
    </div>
</body>
</html>