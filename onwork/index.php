<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Singh Niwas - Luxury Hotel </title>
    <p>Please visit our <a href="https://github.com/singh_niwas/onwork">GitHub repository</a> for more information.</p>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
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

        .logo h1 {
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .logo span {
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
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.7s;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 15px rgba(24,145,209,0.3);
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(24,145,209,0.4);
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

        .menu-toggle { display: none; } 

      
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(rgba(0,0,0,0.8), rgba(0,0,0,0.8)),
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
            top:0; left:0; right:0; bottom:0;
            background: radial-gradient(circle at 20% 50%, rgba(24,145,209,0.2) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(255,107,107,0.1) 0%, transparent 50%);
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
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hero h2 .highlight {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .hero p {
            font-size: 1.3rem;
            color: rgba(255,255,255,0.9);
            margin-bottom: 2.5rem;
            max-width: 600px;
        }
        .hero-buttons {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .hero-buttons .btn { padding: 1rem 2.5rem; font-size: 1.1rem; }

        
        .features {
            padding: 100px 5%;
            background: var(--white);
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
            border: 1px solid rgba(0,0,0,0.05);
        }
        .feature-card::before {
            content:'';
            position: absolute;
            top:0; left:0; right:0; height:5px;
            background: var(--gradient-primary);
            opacity:0;
            transition: var(--transition);
        }
        .feature-card:hover::before { opacity:1; }
        .feature-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); }
        .feature-icon {
            width: 80px; height: 80px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
            color: white; font-size: 2.5rem;
            transition: var(--transition);
        }
        .feature-card:hover .feature-icon { transform: scale(1.1) rotate(5deg); }

     
        .rooms {
            padding: 100px 5%;
            background: var(--background-color);
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
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
        }
        .room-card:hover { transform: translateY(-10px); box-shadow: var(--shadow-lg); }
        .room-image {
            height: 220px;
            background-size: cover;
            background-position: center;
        }
        .room-content {
            padding: 2rem;
        }
        .room-content h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        .room-price {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-color);
            background: rgba(24,145,209,0.1);
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            display: inline-block;
            margin: 0.75rem 0;
        }
        .room-features {
            display: flex;
            gap: 1.5rem;
            margin: 1rem 0;
            color: var(--text-light);
        }
        .room-features span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

    
        footer {
            background: var(--text-dark);
            color: white;
            padding: 80px 5% 40px;
            position: relative;
            overflow: hidden;
        }
        footer::before {
            content:'';
            position: absolute;
            top:0; left:0; right:0; height:5px;
            background: var(--gradient-primary);
        }
        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px,1fr));
            gap: 3rem;
            max-width:1200px;
            margin:0 auto;
        }
        .footer-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 1rem;
        }
        .footer-section h3::after {
            content:'';
            position: absolute;
            bottom:0; left:0; width:50px; height:3px;
            background: var(--gradient-primary);
        }
        .footer-links {
            list-style: none;
        }
        .footer-links li {
            margin-bottom: 0.8rem;
        }
        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap:0.8rem;
            transition: var(--transition);
        }
        .footer-links a:hover {
            color: var(--primary-color);
            transform: translateX(5px);
        }
        .copyright {
            text-align: center;
            margin-top: 4rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.6);
        }

        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to { opacity:1; transform:translateY(0); }
        }

    
        @media (max-width: 768px) {
            .navbar { flex-wrap: wrap; }
            .nav-links { order:3; width:100%; flex-direction: column; gap:0.5rem; margin-top:1rem; }
            .auth-buttons { margin-left:auto; }
            .hero h2 { font-size: 2.5rem; }
        }

        .auth-buttons .btn { transition: all 0.2s; }
        .admin-tag {
            background: #6c757d; border-radius: var(--border-radius); padding:0.5rem 1.5rem;
            display: inline-flex; align-items: center; gap:6px; color:white; text-decoration:none;
            margin-left: 0.5rem;
        }
        .admin-tag:hover { background: #5a6268; }
    </style>
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
           
            <div class="auth-buttons" id="dynamicAuth">
               
            </div>
          
            <a href="#" class="admin-tag" onclick="alert('Admin demo: would redirect to admin-login.php'); return false;">
                <ion-icon name="shield-outline"></ion-icon> Admin
            </a>
        </nav>
    </header>

    <section class="hero" id="home">
        <div class="hero-content">
      
            <h2>Experience <span class="highlight">Luxury</span> & Comfort</h2>
            <p>Welcome to Singh Niwas - Where every stay feels like home. Experience unparalleled hospitality and luxury accommodations in the heart of the city.</p>
            <div class="hero-buttons">
                <a href="#rooms" class="btn btn-primary"><ion-icon name="bed-outline"></ion-icon> View Rooms</a>
                <a href="#contact" class="btn btn-secondary"><ion-icon name="call-outline"></ion-icon> Contact Us</a>
            </div>
            <p style="margin-top:2rem; color:rgba(255,255,255,0.7);">Please visit our <a href="https://github.com/singh_niwas/onwork" style="color:var(--accent-color);">GitHub repository</a> for more information.</p>
        </div>
    </section>

    <section class="features" id="features">
        <div class="section-title">
            <h2>Why Choose Singh Niwas?</h2>
            <p>Experience the best hospitality with our premium amenities</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><ion-icon name="wifi-outline"></ion-icon></div>
                <h3>Free WiFi</h3>
                <p>High-speed internet access throughout the property</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><ion-icon name="fitness-outline"></ion-icon></div>
                <h3>Fitness Center</h3>
                <p>Well-equipped gym for your fitness needs</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><ion-icon name="car-outline"></ion-icon></div>
                <h3>Parking</h3>
                <p>Secure parking space available for all guests</p>
            </div>
        </div>
    </section>

    <section class="rooms" id="rooms">
        <div class="section-title">
            <h2>Our Rooms & Suites</h2>
            <p>Choose from our variety of comfortable accommodations</p>
        </div>
        <div class="rooms-grid" id="roomsContainer">
        </div>
    </section>

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
                    <li><a href="#" id="footerLogin">Login</a></li>
                    <li><a href="#" id="footerRegister">Register</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Contact Us</h3>
                <ul class="footer-links">
                    <li><a href="#"><ion-icon name="location-outline"></ion-icon> Patan, Lalitpur</a></li>
                    <li><a href="tel:+977-9841243527"><ion-icon name="call-outline"></ion-icon> +977-9748418501</a></li>
                    <li><a href="mailto:singhniwas@gmail.com"><ion-icon name="mail-outline"></ion-icon> singhniwas@gmail.com</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            <p>&copy; 2024 Singh Niwas. All rights reserved.</p>
        </div>
    </footer>
    <a href="#" class="back-to-top" id="backToTop" style="position:fixed; bottom:2rem; left:2rem; background:var(--gradient-secondary); color:white; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; text-decoration:none; opacity:0; visibility:hidden; transition:0.3s;" onclick="window.scrollTo({top:0,behavior:'smooth'}); return false;"><ion-icon name="arrow-up-outline"></ion-icon></a>

    <script>
        (function() {
            let loggedIn = false;   


            const logo = document.querySelector('.logo h1');
            logo.style.cursor = 'pointer';
            logo.title = 'Double-click to toggle login state (simulate PHP session)';
            logo.addEventListener('dblclick', function(e) {
                loggedIn = !loggedIn;
                updateUIBasedOnLogin();
                showToast(`Session: ${loggedIn ? 'Logged in (dashboard mode)' : 'Logged out (guest mode)'}`);
            });

            function showToast(msg) {
                let toast = document.createElement('div');
                toast.textContent = msg;
                toast.style.position = 'fixed';
                toast.style.bottom = '20px';
                toast.style.right = '20px';
                toast.style.backgroundColor = 'var(--primary-color)';
                toast.style.color = 'white';
                toast.style.padding = '12px 24px';
                toast.style.borderRadius = '30px';
                toast.style.zIndex = '9999';
                toast.style.boxShadow = 'var(--shadow-lg)';
                toast.style.fontWeight = '600';
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 2000);
            }

            // Room data (same as original)
            const roomsData = [
                { name: 'Standard Room', price: 'NRS 2,500', desc: 'Comfortable room with all basic amenities for a pleasant stay', guests: 2, bed: '1 Double Bed', img: 'https://image-tc.galaxy.tf/wijpeg-f16cnvozo3oq1o8l5bb4ru31k/standard-deluxe-1_wide.jpg?width=1200&crop=0%2C78%2C1600%2C900' },
                { name: 'Deluxe Room', price: 'NRS 4,000', desc: 'Spacious room with balcony and premium amenities', guests: 3, bed: '2 Queen Beds', img: 'https://image-tc.galaxy.tf/wijpeg-43drhllmvuepf1pwg6pt79p6a/hotel-barsey-2024-08-08-21-bis-deluxe-room_wide.jpg?crop=0%2C104%2C2000%2C1125&width=800' },
                { name: 'Executive Suite', price: 'NRS 6,000', desc: 'Luxury suite with separate living area and premium services', guests: 4, bed: 'King Bed + Sofa', img: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRkAiBR8OBv3wdWe7ptDTQbBakewrWreMYjGg&s' }
            ];

            function renderRooms() {
                const container = document.getElementById('roomsContainer');
                container.innerHTML = '';
                roomsData.forEach(room => {
                    const card = document.createElement('div');
                    card.className = 'room-card';
                    card.innerHTML = `
                        <div class="room-image" style="background-image: url('${room.img}')"></div>
                        <div class="room-content">
                            <h3>${room.name}</h3>
                            <div class="room-price">${room.price} / night</div>
                            <p>${room.desc}</p>
                            <div class="room-features">
                                <span><ion-icon name="person-outline"></ion-icon> ${room.guests} Guests</span>
                                <span><ion-icon name="bed-outline"></ion-icon> ${room.bed}</span>
                            </div>
                            ${loggedIn ? 
                                '<a href="#" class="btn btn-primary" onclick="alert(\'Book now – dashboard.php (demo)\'); return false;">Book Now</a>' : 
                                '<a href="#" class="btn btn-primary" onclick="alert(\'Please login first (demo)\'); return false;">Login to Book</a>'}
                        </div>
                    `;
                    container.appendChild(card);
                });
            }

            function updateUIBasedOnLogin() {
                const authDiv = document.getElementById('dynamicAuth');
                if (loggedIn) {
                    authDiv.innerHTML = `
                        <a href="#" class="btn btn-primary" onclick="alert('Dashboard demo'); return false;"><ion-icon name="person-outline"></ion-icon> Dashboard</a>
                        <a href="#" class="btn btn-secondary" onclick="toggleLogout(); return false;">Logout</a>
                    `;
                } else {
                    authDiv.innerHTML = `
                        <a href="#" class="btn btn-secondary" onclick="alert('Login page demo'); return false;">Login</a>
                        <a href="#" class="btn btn-primary" onclick="alert('Register page demo'); return false;">Register</a>
                    `;
                }
            
                document.getElementById('footerLogin').onclick = (e) => { e.preventDefault(); alert(loggedIn ? 'Already logged in' : 'Login page demo'); };
                document.getElementById('footerRegister').onclick = (e) => { e.preventDefault(); alert(loggedIn ? 'Already logged in' : 'Register page demo'); };

            
                renderRooms();
            }

        
            window.toggleLogout = function() {
                loggedIn = false;
                updateUIBasedOnLogin();
                showToast('Logged out (demo)');
            };

            updateUIBasedOnLogin();
 
            document.querySelectorAll('a[href^="#"]:not(.btn)').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            window.addEventListener('scroll', function() {
                const header = document.querySelector('header');
                if (window.scrollY > 100) {
                    header.style.background = 'rgba(255,255,255,0.95)';
                    header.style.backdropFilter = 'blur(10px)';
                } else {
                    header.style.background = 'var(--white)';
                    header.style.backdropFilter = 'none';
                }
                const btt = document.getElementById('backToTop');
                if (window.scrollY > 300) {
                    btt.style.opacity = '1';
                    btt.style.visibility = 'visible';
                } else {
                    btt.style.opacity = '0';
                    btt.style.visibility = 'hidden';
                }
            });

            document.querySelector('header').style.background = 'var(--white)';
        })();
    </script>
</body>
</html>

