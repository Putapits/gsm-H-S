<?php
// Check if session functions are available
if (function_exists('startSecureSession')) {
    startSecureSession();
}
$headerIsLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);
?>
<!DOCTYPE html>
<html lang="en" id="html-root" class="no-transition">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health & Sanitation Services</title>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary': '#4a90e2',
                        'secondary': '#9aa5b1',
                        'accent': '#4caf50',
                        'custom-bg': '#fbfbfb',
                        'green-primary': '#2e7d32',
                        'green-light': '#81c784',
                        'green-dark': '#1b5e20'
                    }
                }
            }
        }
    </script>
    <script>
        (function() {
            try {
                const root = document.documentElement;
                const savedTheme = localStorage.getItem('theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const theme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
                const hadNoTransition = root.classList.contains('no-transition');

                root.classList.remove('light', 'dark');
                root.classList.add(theme);

                if (!hadNoTransition) {
                    root.classList.remove('no-transition');
                }
            } catch (error) {
                console.error('Theme init error:', error);
            }
        })();
    </script>
    <style>
        html, body {
            background-color: #fbfbfb;
            color: #111827;
        }
        html.dark, html.dark body {
            background-color: #111827;
            color: #f3f4f6;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="styles.css">
    
    <!-- Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <!-- Additional CSS for theme toggle -->
    <style>
        /* Ensure theme toggle icons work properly */
        #theme-toggle {
            position: relative;
            z-index: 100;
        }
        
        #theme-toggle svg {
            transition: all 0.2s ease-in-out;
        }
        
        #theme-toggle-dark-icon.hidden,
        #theme-toggle-light-icon.hidden {
            display: none !important;
            opacity: 0;
        }
        
        #theme-toggle-dark-icon:not(.hidden),
        #theme-toggle-light-icon:not(.hidden) {
            display: block !important;
            opacity: 1;
        }
        
        /* Smooth transitions for all theme changes */
        * {
            transition-property: background-color, border-color, color, fill, stroke, opacity;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
        
        /* Prevent transition on page load */
        .no-transition * {
            transition: none !important;
        }
        
        /* Navbar z-index fix - ensure it stays above everything */
        nav {
            z-index: 9999 !important;
        }
        
        /* Map container z-index fix */
        #map {
            z-index: 10 !important;
            height: 400px !important;
            min-height: 400px !important;
            width: 100% !important;
            position: relative !important;
            display: block !important;
            background: #f0f0f0 !important;
        }
        
        @media (min-width: 1024px) {
            #map {
                height: 500px !important;
                min-height: 500px !important;
            }
        }
        
        /* Ensure Leaflet map elements work properly */
        #map .leaflet-container {
            height: 100% !important;
            width: 100% !important;
        }
        
        /* Override any conflicting Tailwind styles */
        #map * {
            box-sizing: border-box !important;
        }
        
        /* Leaflet map controls z-index fix */
        .leaflet-control-container {
            z-index: 100 !important;
        }
        
        .leaflet-popup {
            z-index: 200 !important;
        }
        
        /* Ensure map doesn't interfere with navbar */
        .leaflet-container {
            z-index: 10 !important;
        }
        

        main {
            padding-top: 4rem; /* match nav height to avoid white gap */
        }

        .nav-logo {
            height: 50px;
            width: auto;
        }

        @media (min-width: 768px) {
            .nav-logo {
                height: 60px;
            }
        }
    </style>
    <script>
        window.addEventListener('load', function() {
            document.documentElement.classList.remove('no-transition');
        });
    </script>
</head>
<body class="bg-custom-bg dark:bg-gray-900 text-gray-900 dark:text-white">
    <!-- Navbar -->
    <nav class="bg-primary dark:bg-blue-800 shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <img src="img/logo-hs.png" alt="Health & Sanitation Logo" class="nav-logo mr-4 rounded">
                    <h1 class="text-white text-xl font-bold">Health & Sanitation</h1>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="website.php#home" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <a href="about.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">About</a>
                        
                        <!-- Services Dropdown -->
                        <div class="relative group">
                            <button class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                                Services
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300">
                                <a href="services.php#health-center" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Health Center Services</a>
                                <a href="services.php#sanitation-permit" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Sanitation Permit & Inspection</a>
                                <a href="services.php#immunization" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Immunization & Nutrition Tracker</a>
                                <a href="services.php#wastewater" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Wastewater & Septic Services</a>
                                <a href="services.php#surveillance" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Health Surveillance System</a>
                            </div>
                        </div>
                        
                        <a href="contact.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
                        <?php if ($headerIsLoggedIn): ?>
                            <a href="<?php echo Database::getRoleRedirect($_SESSION['role']); ?>" class="bg-accent hover:bg-green-700 text-white px-3 py-2 rounded-md text-sm font-medium">
                                Go to Dashboard
                            </a>
                            <a href="logout.php" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                        <?php else: ?>
                            <a href="index.html" class="text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Right side controls -->
                <div class="flex items-center space-x-4">
                    <!-- Date and Time -->
                    <div class="text-white text-sm" id="datetime"></div>
                    
                    <!-- Language Selector -->
                    <select id="language-selector" class="bg-blue-700 text-white text-sm rounded px-2 py-1">
                        <option value="en">English</option>
                        <option value="fil">Filipino</option>
                        <option value="ceb">Cebuano</option>
                    </select>
                    
                    <!-- Dark/Light Mode Toggle -->
                    <button id="theme-toggle" class="text-white hover:bg-blue-700 p-2 rounded-md">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:bg-blue-700 p-2 rounded-md">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobile-menu" class="md:hidden hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-blue-700">
                <a href="website.php#home" class="text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="about.php" class="text-white block px-3 py-2 rounded-md text-base font-medium">About</a>
                <a href="services.php#health-center" class="text-white block px-3 py-2 rounded-md text-base font-medium">Health Center Services</a>
                <a href="services.php#sanitation-permit" class="text-white block px-3 py-2 rounded-md text-base font-medium">Sanitation Permit & Inspection</a>
                <a href="services.php#immunization" class="text-white block px-3 py-2 rounded-md text-base font-medium">Immunization & Nutrition Tracker</a>
                <a href="services.php#wastewater" class="text-white block px-3 py-2 rounded-md text-base font-medium">Wastewater & Septic Services</a>
                <a href="services.php#surveillance" class="text-white block px-3 py-2 rounded-md text-base font-medium">Health Surveillance System</a>
                <a href="contact.php" class="text-white block px-3 py-2 rounded-md text-base font-medium">Contact</a>
                <?php if ($headerIsLoggedIn): ?>
                    <a href="<?php echo Database::getRoleRedirect($_SESSION['role']); ?>" class="bg-accent hover:bg-green-700 text-white block px-3 py-2 rounded-md text-base font-medium">
                        Go to Dashboard
                    </a>
                    <a href="logout.php" class="text-white block px-3 py-2 rounded-md text-base font-medium">Logout</a>
                <?php else: ?>
                    <a href="website.php#appointment" class="bg-accent hover:bg-green-700 text-white block px-3 py-2 rounded-md text-base font-medium">Book Appointment</a>
                    <a href="index.html" class="text-white block px-3 py-2 rounded-md text-base font-medium">Login</a>
                    <a href="register.php" class="text-white block px-3 py-2 rounded-md text-base font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        (function() {
            const root = document.documentElement;
            const themeToggle = document.getElementById('theme-toggle');
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            const lightIcon = document.getElementById('theme-toggle-light-icon');

            if (!themeToggle || !darkIcon || !lightIcon) {
                console.error('Theme toggle elements not found');
                return;
            }

            function applyTheme(theme) {
                console.log('Applying theme:', theme);
                root.classList.remove('light', 'dark');
                root.classList.add(theme);
                localStorage.setItem('theme', theme);
                console.log('HTML classes after apply:', root.className);

                if (theme === 'dark') {
                    darkIcon.classList.remove('hidden');
                    lightIcon.classList.add('hidden');
                } else {
                    darkIcon.classList.add('hidden');
                    lightIcon.classList.remove('hidden');
                }
            }

            const storedTheme = localStorage.getItem('theme');
            const currentTheme = storedTheme || (root.classList.contains('dark') ? 'dark' : 'light');
            console.log('Initial theme:', currentTheme);
            applyTheme(currentTheme);

            themeToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const nextTheme = root.classList.contains('dark') ? 'light' : 'dark';
                console.log('Toggle clicked, switching to:', nextTheme);
                applyTheme(nextTheme);
            });
        })();
    </script>
