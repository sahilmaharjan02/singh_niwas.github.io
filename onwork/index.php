<?php
session_start();
?>
<?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
    <a href="dashboard.php" class="btn btn-primary">
        <ion-icon name="person-outline"></ion-icon>
        Dashboard
    </a>
    <a href="logout.php" class="btn btn-secondary">Logout</a>
<?php else: ?>
    <a href="login.php" class="btn btn-secondary">Login</a>
    <a href="register.php" class="btn btn-primary">Register</a>
<?php endif; ?>

<a href="admin/admin-login.php" class="btn btn-secondary" style="background: #6c757d;">
    <ion-icon name="shield-outline"></ion-icon>
    Admin
</a>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <p>Please visit our <a href="https://github.com/singh_niwas/onwork">GitHub repository</a> for more information.</p>
    <title>Singh Niwas - Luxury Hotel</title>
    <style>
:root {
    --primary-color: #1891d1;
    --primary-dark: #1d64c2;
    --secondary-color: #ff6b6b;
    --accent-color: #ffd166;
    --background-color: #f8f9fa;
    --text-dark: #002960;
    --text-light: #666;
    --white: #ffffff;
    --gradient-primary: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    --gradient-secondary: linear-gradient(135deg, var(--secondary-color), #ff4757);
    --gradient-accent: linear-gradient(135deg, var(--accent-color), #ff9e42);
    --shadow-sm: 0 2px 10px rgba(0, 0, 0, 0.1);
    --shadow-md: 0 5px 20px rgba(0, 0, 0, 0.15);
    --shadow-lg: 0 10px 30px rgba(0, 0, 0, 0.2);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --border-radius: 12px;
    --border-radius-lg: 20px;
    --border-radius-xl: 30px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
    line-height: 1.7;
    color: var(--text-dark);
    background: var(--background-color);
    overflow-x: hidden;
}

/* Modern Scrollbar */
::-webkit-scrollbar {
    width: 10px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 5px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--primary-dark);
}

/* Header & Navigation */
header {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: var(--shadow-sm);
    position: fixed;
    width: 100%;
    top: 0;
    z-index: 1000;
    transition: var(--transition);
}

header.scrolled {
    background: rgba(255, 255, 255, 0.98);
    box-shadow: var(--shadow-md);
    padding: 0.5rem 0;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 5%;
    max-width: 1400px;
    margin: 0 auto;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.logo-icon {
    width: 40px;
    height: 40px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
}

.logo-text h1 {
    font-size: 1.8rem;
    color: var(--text-dark);
    font-weight: 800;
    letter-spacing: -0.5px;
}

.logo-text span {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-links {
    display: flex;
    gap: 2rem;
    list-style: none;
}

.nav-links a {
    text-decoration: none;
    color: var(--text-dark);
    font-weight: 600;
    font-size: 1rem;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    transition: var(--transition);
    position: relative;
}

.nav-links a::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 3px;
    background: var(--gradient-primary);
    border-radius: 3px;
    transition: var(--transition);
    transform: translateX(-50%);
}

.nav-links a:hover {
    color: var(--primary-color);
}

.nav-links a:hover::after {
    width: 80%;
}

.auth-buttons {
    display: flex;
    gap: 1rem;
}

.btn {
    padding: 0.8rem 2rem;
    border-radius: var(--border-radius);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.95rem;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.7s;
}

.btn:hover::before {
    left: 100%;
}

.btn-primary {
    background: var(--gradient-primary);
    color: white;
    box-shadow: 0 4px 15px rgba(24, 145, 209, 0.3);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(24, 145, 209, 0.4);
}

.btn-secondary {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-3px);
}

.menu-toggle {
    display: none;
    background: none;
    border: none;
    font-size: 1.8rem;
    color: var(--text-dark);
    cursor: pointer;
}

/* Hero Section */
.hero {
    min-height: 100vh;
    display: flex;
    align-items: center;
    background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.8)),
                url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80') no-repeat center center;
    background-size: cover;
    background-attachment: fixed;
    position: relative;
    overflow: hidden;
    padding: 100px 5% 50px;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 50%, rgba(24, 145, 209, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 107, 0.1) 0%, transparent 50%);
}

.hero-content {
    max-width: 800px;
    position: relative;
    z-index: 1;
    animation: fadeInUp 1s ease;
}

.hero h2 {
    font-size: 4rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero h2 .highlight {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero p {
    font-size: 1.3rem;
    color: rgba(255, 255, 255, 0.9);
    margin-bottom: 2.5rem;
    max-width: 600px;
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.hero-buttons .btn {
    padding: 1rem 2.5rem;
    font-size: 1.1rem;
}

.hero-stats {
    display: flex;
    gap: 3rem;
    margin-top: 4rem;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: white;
    margin-bottom: 0.5rem;
    display: block;
}

.stat-label {
    color: rgba(255, 255, 255, 0.8);
    font-size: 1rem;
}

.features {
    padding: 100px 5%;
    background: var(--white);
    position: relative;
}

.section-title {
    text-align: center;
    margin-bottom: 4rem;
}

.section-title h2 {
    font-size: 3rem;
    font-weight: 800;
    color: var(--text-dark);
    margin-bottom: 1rem;
    position: relative;
    display: inline-block;
}

.section-title h2::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--gradient-primary);
    border-radius: 2px;
}

.section-title p {
    color: var(--text-light);
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
}

.feature-card {
    background: var(--white);
    padding: 2.5rem 2rem;
    border-radius: var(--border-radius-lg);
    box-shadow: var(--shadow-sm);
    transition: var(--transition);
    text-align: center;
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--gradient-primary);
    opacity: 0;
    transition: var(--transition);
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
}

.feature-card:hover::before {
    opacity: 1;
}

.feature-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient-primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    color: white;
    font-size: 2.5rem;
    transition: var(--transition);
}

.feature-card:hover .feature-icon {
    transform: scale(1.1) rotate(5deg);
}

.feature-card h3 {
    font-size: 1.5rem;
    margin-bottom: 1rem;
    color: var(--text-dark);
}

.feature-card p {
    color: var(--text-light);
    font-size: 1rem;
}


.rooms {
    padding: 100px 5%;
    background: var(--background-color);
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 2.5rem;
    max-width: 1200px;
    margin: 0 auto;
}

.room-card {
    background: var(--white);
    border-radius: var(--border-radius-lg);
    overflow: hidden;
    box-shadow: var(--shadow-md);
    transition: var(--transition);
    position: relative;
}

.room-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-lg);
}

.room-image {
    height: 250px;
    background-size: cover;
    background-position: center;
    position: relative;
    overflow: hidden;
}

.room-image::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 50%, rgba(0, 0, 0, 0.7));
}

.room-content {
    padding: 2rem;
}

.room-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.room-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-dark);
}

.room-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--primary-color);
    background: rgba(24, 145, 209, 0.1);
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
}

.room-price span {
    font-size: 1rem;
    color: var(--text-light);
    font-weight: 500;
}

.room-features {
    display: flex;
    gap: 1.5rem;
    margin: 1.5rem 0;
    color: var(--text-light);
}

.room-features span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}

.room-features ion-icon {
    color: var(--primary-color);
    font-size: 1.2rem;
}

.room-description {
    color: var(--text-light);
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.room-card .btn {
    width: 100%;
    justify-content: center;
    padding: 1rem;
    font-weight: 600;
}

.testimonials {
    padding: 100px 5%;
    background: var(--white);
}

.testimonial-slider {
    max-width: 800px;
    margin: 0 auto;
    position: relative;
}

.testimonial-card {
    background: var(--background-color);
    padding: 2.5rem;
    border-radius: var(--border-radius-lg);
    text-align: center;
    box-shadow: var(--shadow-sm);
    margin: 0 1rem;
}

.testimonial-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    margin: 0 auto 1.5rem;
    overflow: hidden;
    border: 4px solid var(--white);
    box-shadow: var(--shadow-sm);
}

.testimonial-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.testimonial-text {
    font-size: 1.1rem;
    font-style: italic;
    color: var(--text-dark);
    margin-bottom: 1.5rem;
    line-height: 1.8;
}

.testimonial-author {
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.testimonial-rating {
    color: #ffc107;
    font-size: 1.2rem;
    margin-top: 0.5rem;
}


footer {
    background: var(--text-dark);
    color: white;
    padding: 80px 5% 40px;
    position: relative;
    overflow: hidden;
}

footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: var(--gradient-primary);
}

.footer-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 3rem;
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

.footer-section h3 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: white;
    position: relative;
    padding-bottom: 1rem;
}

.footer-section h3::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--gradient-primary);
}

.footer-section p {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.footer-links {
    list-style: none;
}

.footer-links li {
    margin-bottom: 0.8rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.footer-links a:hover {
    color: var(--primary-color);
    transform: translateX(5px);
}

.footer-social {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.social-icon {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-decoration: none;
    transition: var(--transition);
}

.social-icon:hover {
    background: var(--primary-color);
    transform: translateY(-3px);
}

.copyright {
    text-align: center;
    margin-top: 4rem;
    padding-top: 2rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
}

.floating-cta {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: var(--gradient-primary);
    color: white;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    box-shadow: var(--shadow-lg);
    z-index: 100;
    transition: var(--transition);
}

.floating-cta:hover {
    transform: scale(1.1) rotate(15deg);
    box-shadow: 0 8px 25px rgba(24, 145, 209, 0.4);
}

.back-to-top {
    position: fixed;
    bottom: 2rem;
    left: 2rem;
    background: var(--gradient-secondary);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    box-shadow: var(--shadow-md);
    z-index: 100;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
}

.back-to-top.visible {
    opacity: 1;
    visibility: visible;
}

.back-to-top:hover {
    transform: translateY(-5px);
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes float {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}


@media (max-width: 1024px) {
    .hero h2 {
        font-size: 3rem;
    }
    
    .section-title h2 {
        font-size: 2.5rem;
    }
}

@media (max-width: 768px) {
    .navbar {
        padding: 1rem;
    }
    
    .nav-links {
        position: fixed;
        top: 80px;
        left: 0;
        right: 0;
        background: white;
        flex-direction: column;
        padding: 2rem;
        box-shadow: var(--shadow-md);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
        z-index: 999;
    }
    
    .nav-links.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
    
    .menu-toggle {
        display: block;
    }
    
    .hero {
        padding: 80px 5% 30px;
        background-attachment: scroll;
    }
    
    .hero h2 {
        font-size: 2.5rem;
    }
    
    .hero p {
        font-size: 1.1rem;
    }
    
    .hero-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .rooms-grid {
        grid-template-columns: 1fr;
    }
    
    .floating-cta {
        bottom: 1rem;
        right: 1rem;
        width: 50px;
        height: 50px;
    }
    
    .back-to-top {
        bottom: 1rem;
        left: 1rem;
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .hero h2 {
        font-size: 2rem;
    }
    
    .hero-stats {
        gap: 1.5rem;
        justify-content: center;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .section-title h2 {
        font-size: 2rem;
    }
    
    .feature-card,
    .room-card,
    .testimonial-card {
        padding: 1.5rem;
    }
}


.loading {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.parallax {
    background-attachment: fixed;
    background-position: center;
    background-repeat: no-repeat;
    background-size: cover;
}

.glass {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.gradient-text {
    background: var(--gradient-primary);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}


.tooltip {
    position: relative;
    cursor: pointer;
}

.tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--text-dark);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: var(--border-radius);
    font-size: 0.85rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: var(--transition);
    z-index: 1000;
}

.tooltip:hover::after {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 10px);
}


.custom-checkbox {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.custom-checkbox input {
    display: none;
}

.custom-checkbox .checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid var(--primary-color);
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
    transition: var(--transition);
}

.custom-checkbox input:checked + .checkmark {
    background: var(--primary-color);
}

.custom-checkbox input:checked + .checkmark::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: bold;
}
</style>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>Singh <span>Niwas</span></h1>
            </div>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#rooms">Rooms</a></li>
                <li><a href="#features">Features</a></li>
                <li><a href="#contact">Contact</a></li>
            </ul>
            <div class="auth-buttons">
                <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                    <a href="dashboard.php" class="btn btn-primary">
                        <ion-icon name="person-outline"></ion-icon>
                        Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="hero-content">
            <h2>Experience Luxury & Comfort</h2>
            <p>Welcome to Singh Niwas - Where every stay feels like home. Experience unparalleled hospitality and luxury accommodations in the heart of the city.</p>
            <div class="hero-buttons">
                <a href="#rooms" class="btn btn-primary">
                    <ion-icon name="bed-outline"></ion-icon>
                    View Rooms
                </a>
                <a href="#contact" class="btn btn-secondary">
                    <ion-icon name="call-outline"></ion-icon>
                    Contact Us
                </a>
            </div>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-title">
            <h2>Why Choose Singh Niwas?</h2>
            <p>Experience the best hospitality with our premium amenities</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <ion-icon name="wifi-outline"></ion-icon>
                </div>
                <h3>Free WiFi</h3>
                <p>High-speed internet access throughout the property</p>
            </div>
              <div class="feature-card">
                <div class="feature-icon">
                    <ion-icon name="fitness-outline"></ion-icon>
                </div>
                <h3>Fitness Center</h3>
                <p>Well-equipped gym for your fitness needs</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <ion-icon name="car-outline"></ion-icon>
                </div>
                <h3>Parking</h3>
                <p>Secure parking space available for all guests</p>
            </div>  
        </div>
    </section>

    <!-- Rooms Section -->
    <section class="rooms" id="rooms">
        <div class="section-title">
            <h2>Our Rooms & Suites</h2>
            <p>Choose from our variety of comfortable accommodations</p>
        </div>
        <div class="rooms-grid">
            <div class="room-card">
                <div class="room-image" style="background-image: url('https://image-tc.galaxy.tf/wijpeg-f16cnvozo3oq1o8l5bb4ru31k/standard-deluxe-1_wide.jpg?width=1200&crop=0%2C78%2C1600%2C900')"></div>
                <div class="room-content">
                    <h3>Standard Room</h3>
                    <div class="room-price">NRS 2,500 / night</div>
                    <p>Comfortable room with all basic amenities for a pleasant stay</p>
                    <div class="room-features">
                        <span><ion-icon name="person-outline"></ion-icon> 2 Guests</span>
                        <span><ion-icon name="bed-outline"></ion-icon> 1 Double Bed</span>
                    </div>
                    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="dashboard.php" class="btn btn-primary">Book Now</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="room-card">
                <div class="room-image" style="background-image: url('https://image-tc.galaxy.tf/wijpeg-43drhllmvuepf1pwg6pt79p6a/hotel-barsey-2024-08-08-21-bis-deluxe-room_wide.jpg?crop=0%2C104%2C2000%2C1125&width=800')"></div>
                <div class="room-content">
                    <h3>Deluxe Room</h3>
                    <div class="room-price">NRS 4,000 / night</div>
                    <p>Spacious room with balcony and premium amenities</p>
                    <div class="room-features">
                        <span><ion-icon name="person-outline"></ion-icon> 3 Guests</span>
                        <span><ion-icon name="bed-outline"></ion-icon> 2 Queen Beds</span>
                    </div>
                    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="dashboard.php" class="btn btn-primary">Book Now</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="room-card">
                <div class="room-image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRkAiBR8OBv3wdWe7ptDTQbBakewrWreMYjGg&s')"></div>
                <div class="room-content">
                    <h3>Executive Suite</h3>
                    <div class="room-price">NRS 6,000 / night</div>
                    <p>Luxury suite with separate living area and premium services</p>
                    <div class="room-features">
                        <span><ion-icon name="person-outline"></ion-icon> 4 Guests</span>
                        <span><ion-icon name="bed-outline"></ion-icon> King Bed + Sofa</span>
                    </div>
                    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                        <a href="dashboard.php" class="btn btn-primary">Book Now</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login to Book</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Singh Niwas</h3>
                <p>Experience luxury and comfort like never before. We're committed to making your stay memorable.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="#home">Home</a></li>
                    <li><a href="#rooms">Rooms</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="#"><ion-icon name="location-outline"></ion-icon> Patan, Lalitpur</a></li>
                    <li><a href="tel:+977-9841243527"><ion-icon name="call-outline"></ion-icon> +977-9841243527</a></li>
                    <li><a href="mailto:singhniwas@gmail.com"><ion-icon name="mail-outline"></ion-icon> singhniwas@gmail.com</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Singh Niwas. All rights reserved.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.background = 'var(--white)';
                header.style.backdropFilter = 'none';
            }
        });
    </script>
</body>

</html>
