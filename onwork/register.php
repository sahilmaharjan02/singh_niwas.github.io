<?php
session_start();
require_once 'db_connection.php';

$error = '';
$success = '';
$name = $email = $phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Full name is required';
    } elseif (strlen($name) < 2) {
        $errors[] = 'Name must be at least 2 characters long';
    } elseif (strlen($name) > 100) {
        $errors[] = 'Name must be less than 100 characters';
    } elseif (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
        $errors[] = 'Name can only contain letters, spaces, hyphens and apostrophes';
    }
    
 
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email must be less than 255 characters';
    }
    
    if (!empty($phone)) {
        $phone = preg_replace('/[^0-9+]/', '', $phone); 
        if (!preg_match('/^[+]?[0-9]{10,15}$/', $phone)) {
            $errors[] = 'Phone number must be 10-15 digits ';
        }
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    } elseif (strlen($password) > 72) {
        $errors[] = 'Password must be less than 72 characters';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    // } elseif (!preg_match('@', $password)) {
    //     $errors[] = 'Password must contain at least one special character (@ etc.)';
    }
    
    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$name, $email, $phone, $hashed_password])) {
                $success = 'Registration successful! You can now login.';
                // Clear form data on success
                $name = $email = $phone = '';
                header("refresh:2;url=login.php");
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    } else {
        $error = implode('<br>', $errors);
    }
}

$validation_js = "
<script>
function validateRegistrationForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    const nameRegex = /^[a-zA-Z\\s'-]+$/;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    const phoneRegex = /^[+]?[0-9]{10,15}$/;
    const uppercaseRegex = /[A-Z]/;
    const lowercaseRegex = /[a-z]/;
    const numberRegex = /[0-9]/;
    // const specialCharRegex = /[!@#$%^&*(),.?\":{}|<>]/;
    
    // Name validation
    if (!name) {
        alert('Full name is required');
        return false;
    }
    if (name.length < 2) {
        alert('Name must be at least 2 characters long');
        return false;
    }
    if (name.length > 100) {
        alert('Name must be less than 100 characters');
        return false;
    }
    if (!nameRegex.test(name)) {
        alert('Name can only contain letters, spaces, hyphens and apostrophes');
        return false;
    }
    
    // Email validation
    if (!email) {
        alert('Email is required');
        return false;
    }
    if (!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return false;
    }
    if (email.length > 255) {
        alert('Email must be less than 255 characters');
        return false;
    }
    
    // Phone validation (optional)
    if (phone) {
        const cleanPhone = phone.replace(/[^0-9+]/g, '');
        if (!phoneRegex.test(cleanPhone)) {
            alert('Phone number must be 10-15 digits ');
            return false;
        }
    }
    
    // Password validation
    if (!password) {
        alert('Password is required');
        return false;
    }
    if (password.length < 6) {
        alert('Password must be at least 6 characters long');
        return false;
    }
    if (password.length > 72) {
        alert('Password must be less than 72 characters');
        return false;
    }
    if (!uppercaseRegex.test(password)) {
        alert('Password must contain at least one uppercase letter');
        return false;
    }
    if (!lowercaseRegex.test(password)) {
        alert('Password must contain at least one lowercase letter');
        return false;
    }
    if (!numberRegex.test(password)) {
        alert('Password must contain at least one number');
        return false;
    }
    // if (!specialCharRegex.test(password)) {
    //     alert('Password must contain at least one special character (@etc.)');
    //     return false;
    // }
    
    // Confirm password
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return false;
    }
    
    return true;
}
</script>
";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Singh Niwas</title>
    <style>
        :root {
            --primary-color: #1891d1;
            --primary-dark: #1d64c2;
            --background-color: #e6f2ff;
            --text-dark: #002960;
            --white: #ffffff;
            --color-button-success: #28a745;
            --color-button-danger: #dc3545;
            --color-confirmed-bg: #d4edda;
            --color-confirmed-text: #155724;
            --color-cancelled-bg: #f8d7da;
            --color-cancelled-text: #721c24;
        }

        .register-body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.7)),
                        url('https://source.unsplash.com/1920x1080/?hotel-lobby') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: 20px 0;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
            position: relative;
        }

        .register-card {
            background: var(--white);
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            animation: card-appear 0.5s ease;
        }

        @keyframes card-appear {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .register-header h1 {
            margin: 0;
            color: var(--text-dark);
            font-size: 26px;
            font-weight: 600;
        }

        .register-header p {
            margin: 8px 0 0;
            color: var(--text-dark);
            opacity: 0.8;
            font-size: 15px;
        }

        .register-input-group {
            margin-bottom: 16px;
        }

        .register-input-group label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-dark);
            font-size: 14px;
            font-weight: 500;
        }

        .register-input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .register-input-icon {
            position: absolute;
            left: 12px;
            color: var(--text-dark);
            opacity: 0.6;
            font-size: 18px;
        }

        .register-input-wrapper input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1.5px solid var(--background-color);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }

        .register-input-wrapper input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(24, 145, 209, 0.2);
            outline: none;
        }

        .register-toggle-password {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: var(--text-dark);
            opacity: 0.6;
            font-size: 18px;
            transition: opacity 0.3s ease;
        }

        .register-toggle-password:hover {
            opacity: 1;
        }

        .register-button {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            margin-top: 10px;
        }

        .register-button:hover {
            background: var(--primary-dark);
        }

        .register-button:active {
            transform: scale(0.98);
        }

        .register-login {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 0;
            color: var(--text-dark);
            font-size: 14px;
        }

        .register-login a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .register-login a:hover {
            text-decoration: underline;
        }

        .register-back-home {
            position: absolute;
            top: -40px;
            left: 20px;
            color: var(--white);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            transition: opacity 0.3s ease;
        }

        .register-back-home:hover {
            opacity: 0.8;
        }

        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 14px;
            line-height: 1.5;
        }

        .success {
            background: var(--color-confirmed-bg);
            color: var(--color-confirmed-text);
        }

        .error {
            background: var(--color-cancelled-bg);
            color: var(--color-cancelled-text);
        }

        .password-requirements {
            font-size: 12px;
            color: var(--text-dark);
            opacity: 0.7;
            margin-top: 5px;
            padding-left: 5px;
        }

        .password-requirements ul {
            margin: 5px 0 0;
            padding-left: 20px;
        }

        .password-requirements li {
            margin-bottom: 2px;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 16px;
            }
            .register-card {
                padding: 24px;
            }
            .register-header h1 {
                font-size: 24px;
            }
        }
    </style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <?php echo $validation_js; ?>
</head>
<body class="register-body">
    <div class="register-container">
        <a href="index.php" class="register-back-home">
            <ion-icon name="arrow-back-outline"></ion-icon>
            Back to Home
        </a>
        
        <div class="register-card">
            <div class="register-header">
                <h1>Create Account</h1>
                <p>Join Singh Niwas family</p>
            </div>

            <?php if ($error): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" onsubmit="return validateRegistrationForm()">
                <div class="register-input-group">
                    <label for="name">Full Name *</label>
                    <div class="register-input-wrapper">
                        <ion-icon name="person-outline" class="register-input-icon"></ion-icon>
                        <input type="text" id="name" name="name" required 
                               placeholder="Enter your full name" 
                               value="<?php echo htmlspecialchars($name); ?>"
                               maxlength="100"
                               pattern="[a-zA-Z\s'-]+"
                               title="Name can only contain letters, spaces, hyphens and apostrophes">
                    </div>
                </div>

                <div class="register-input-group">
                    <label for="email">Email *</label>
                    <div class="register-input-wrapper">
                        <ion-icon name="mail-outline" class="register-input-icon"></ion-icon>
                        <input type="email" id="email" name="email" required 
                               placeholder="Enter your email" 
                               value="<?php echo htmlspecialchars($email); ?>"
                               maxlength="255">
                    </div>
                </div>

                <div class="register-input-group">
                    <label for="phone">Phone Number</label>
                    <div class="register-input-wrapper">
                        <ion-icon name="call-outline" class="register-input-icon"></ion-icon>
                        <input type="tel" id="phone" name="phone" 
                               placeholder="Enter your phone number (10-15 digits)" 
                               value="<?php echo htmlspecialchars($phone); ?>"
                               pattern="[+]?[0-9]{10,15}"
                               title="Phone number must be 10-15 digits ">
                    </div>
                </div>

                <div class="register-input-group">
                    <label for="password">Password *</label>
                    <div class="register-input-wrapper">
                        <ion-icon name="lock-closed-outline" class="register-input-icon"></ion-icon>
                        <input type="password" id="password" name="password" required 
                               placeholder="Enter your password"
                               minlength="6"
                               maxlength="72">
                        <ion-icon name="eye-off-outline" class="register-toggle-password" id="togglePassword"></ion-icon>
                    </div>
                    <div class="password-requirements">
                        Password must contain:
                        <ul>
                            <li>At least 6 characters</li>
                            <li>One uppercase letter</li>
                            <li>One lowercase letter</li>
                            <li>One number</li>
                            <!-- <li>One special character (@ etc.)</li> -->
                        </ul>
                    </div>
                </div>

                <div class="register-input-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="register-input-wrapper">
                        <ion-icon name="lock-closed-outline" class="register-input-icon"></ion-icon>
                        <input type="password" id="confirm_password" name="confirm_password" required 
                               placeholder="Confirm your password"
                               minlength="6"
                               maxlength="72">
                        <ion-icon name="eye-off-outline" class="register-toggle-password" id="toggleConfirmPassword"></ion-icon>
                    </div>
                </div>

                <button type="submit" class="register-button">
                    <span>Create Account</span>
                    <ion-icon name="person-add-outline"></ion-icon>
                </button>

                <p class="register-login">
                    Already have an account? 
                    <a href="login.php">Sign In</a>
                </p>
            </form>
        </div>
    </div>

    <script>

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this;
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.setAttribute('name', 'eye-outline');
            } else {
                passwordInput.type = 'password';
                icon.setAttribute('name', 'eye-off-outline');
            }
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPasswordInput = document.getElementById('confirm_password');
            const icon = this;
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                icon.setAttribute('name', 'eye-outline');
            } else {
                confirmPasswordInput.type = 'password';
                icon.setAttribute('name', 'eye-off-outline');
            }
        });

        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                // special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };
        
        });
    </script>
</body>
</html>