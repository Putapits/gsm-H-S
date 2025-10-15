<?php
// Secure session and authentication
require_once '../include/database.php';
startSecureSession();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../index.html');
    exit();
}

// Verify user role is 'citizen'
if ($_SESSION['role'] !== 'citizen') {
    // Redirect to appropriate dashboard based on role
    header('Location: ' . Database::getRoleRedirect($_SESSION['role']));
    exit();
}

// Additional security: Verify session user still exists and is active
$user = $database->getUserById($_SESSION['user_id']);
if (!$user || $user['status'] !== 'active') {
    session_destroy();
    header('Location: ../index.html?error=account_inactive');
    exit();
}

// Get the current page from URL parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Define allowed pages
$allowed_pages = ['home', 'about', 'services', 'contact', 'profile'];

// Validate page parameter
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
?>
<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citizen Portal - Health & Sanitation</title>
    <script>
        (function() {
            const root = document.documentElement;
            const storedTheme = localStorage.getItem('theme');
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = storedTheme ? storedTheme : (prefersDark ? 'dark' : 'light');
            root.classList.remove('light', 'dark');
            root.classList.add(theme);
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary': '#4a90e2',
                        'secondary': '#9aa5b1',
                        'accent': '#4caf50'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .floating-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .active-nav {
            background-color: rgba(74, 144, 226, 0.1);
            border-left: 4px solid #4a90e2;
        }
        
        /* Citizen portal content styling */
        .citizen-portal-content {
            padding-top: 0 !important;
        }
        
        /* Hide any duplicate navigation elements that might slip through */
        .citizen-portal-content nav,
        .citizen-portal-content header {
            display: none !important;
        }
        
        /* Ensure sections start from the top without extra spacing */
        .citizen-portal-content section:first-child {
            padding-top: 2rem !important;
        }

        .portal-logo {
            height: 50px;
            width: auto;
        }
        @media (min-width: 768px) {
            .portal-logo {
                height: 58px;
            }
        }

        .chatbot-shadow {
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.25);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors duration-300">
    <!-- Navigation Header -->
    <nav class="bg-white dark:bg-gray-800 shadow-lg border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <img src="../img/logo-hs.png" alt="Health & Sanitation Logo" class="portal-logo rounded">
                    </div>
                    <div class="ml-3">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Health & Sanitation</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Citizen Portal</p>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="?page=home" class="<?php echo $page === 'home' ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Home
                        </a>
                        <a href="?page=about" class="<?php echo $page === 'about' ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            About
                        </a>
                        <a href="?page=services" class="<?php echo $page === 'services' ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Services
                        </a>
                        <a href="?page=contact" class="<?php echo $page === 'contact' ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Contact
                        </a>
                        <a href="?page=profile" class="<?php echo $page === 'profile' ? 'bg-primary text-white' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'; ?> px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Profile
                        </a>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="bg-gray-100 dark:bg-gray-700 p-2 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-gray-800 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="w-5 h-5 text-gray-800 dark:text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    <!-- User Dropdown -->
                    <div class="relative">
                        <button class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-primary" id="user-menu-button">
                            <div class="h-8 w-8 bg-primary rounded-full flex items-center justify-center">
                                <span class="text-white font-medium"><?php echo strtoupper(substr($_SESSION['first_name'] ?? 'U', 0, 1)); ?></span>
                            </div>
                            <span class="ml-2 text-gray-700 dark:text-gray-300"><?php echo $_SESSION['first_name'] ?? 'User'; ?></span>
                        </button>
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 z-50">
                            <a href="?page=profile" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Your Profile</a>
                            <a href="../logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Sign out</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
        <?php
        switch($page) {
            case 'home':
                // Include website.php content but extract only main content
                echo '<div class="citizen-portal-content">';
                ob_start();
                include '../website.php';
                $content = ob_get_clean();
                // Adjust "Learn More" links to navigate to services page
                $content = str_replace(
                    [
                        'href="/capstone-HS/citizen/citizen.php?page=services#health-center"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#sanitation-permit"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#immunization"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#wastewater"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#surveillance"'
                    ],
                    [
                        'href="?page=services#health-center-details" data-service-target="health-center-details"',
                        'href="?page=services#sanitation-permit" data-service-target="sanitation-permit"',
                        'href="?page=services#immunization" data-service-target="immunization"',
                        'href="?page=services#wastewater" data-service-target="wastewater"',
                        'href="?page=services#surveillance" data-service-target="surveillance"'
                    ],
                    $content
                );
                // Extract only the main content section (everything after </nav> and before </body>)
                if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                    echo $matches[1];
                } else {
                    // Fallback: remove everything before first <section> and after last </section>
                    $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                    echo $content;
                }
                echo '</div>';
                break;
            case 'about':
                echo '<div class="citizen-portal-content">';
                ob_start();
                include '../about.php';
                $content = ob_get_clean();
                // Extract only the main content section
                if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                    echo $matches[1];
                } else {
                    // Fallback: remove everything before first content section
                    $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                    echo $content;
                }
                echo '</div>';
                break;
            case 'services':
                echo '<div class="citizen-portal-content">';
                // Add service category dropdown
                echo '<div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4 mb-6">';
                echo '<div class="max-w-7xl mx-auto">';
                echo '<div class="flex items-center justify-between">';
                echo '<h2 class="text-2xl font-bold text-gray-900 dark:text-white">Our Services</h2>';
                echo '<div class="flex items-center space-x-4">';
                echo '<label for="service-category" class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Service Category:</label>';
                echo '<select id="service-category" class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white text-sm rounded-lg focus:ring-primary focus:border-primary block w-64 p-2.5">';
                echo '<option value="all">All Services</option>';
                echo '<option value="health-center-details">Health Center Services</option>';
                echo '<option value="sanitation-permit">Sanitation Permit & Inspection</option>';
                echo '<option value="immunization">Immunization & Nutrition</option>';
                echo '<option value="wastewater">Wastewater & Septic Services</option>';
                echo '<option value="surveillance">Health Surveillance System</option>';
                echo '</select>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
                ob_start();
                include '../services.php';
                $content = ob_get_clean();
                // Adjust "Learn More" links to target sections within citizen portal
                $content = str_replace(
                    [
                        'href="/capstone-HS/citizen/citizen.php?page=services#health-center"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#sanitation-permit"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#immunization"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#wastewater"',
                        'href="/capstone-HS/citizen/citizen.php?page=services#surveillance"'
                    ],
                    [
                        'href="?page=services#health-center-details" data-service-target="health-center-details"',
                        'href="?page=services#sanitation-permit" data-service-target="sanitation-permit"',
                        'href="?page=services#immunization" data-service-target="immunization"',
                        'href="?page=services#wastewater" data-service-target="wastewater"',
                        'href="?page=services#surveillance" data-service-target="surveillance"'
                    ],
                    $content
                );
                // Extract only the main content section
                if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                    echo $matches[1];
                } else {
                    // Fallback: remove everything before first content section
                    $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                    echo $content;
                }
                echo '</div>';
                break;
            case 'contact':
                echo '<div class="citizen-portal-content">';
                ob_start();
                include '../contact.php';
                $content = ob_get_clean();
                // Extract only the main content section
                if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                    echo $matches[1];
                } else {
                    // Fallback: remove everything before first content section
                    $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                    echo $content;
                }
                echo '</div>';
                break;
            case 'profile':
                include 'profile.php';
                break;
            default:
                echo '<div class="citizen-portal-content">';
                ob_start();
                include '../website.php';
                $content = ob_get_clean();
                // Extract only the main content section
                if (preg_match('/<main[^>]*>(.*?)<\/main>/s', $content, $matches)) {
                    echo $matches[1];
                } else {
                    // Fallback: remove everything before first content section
                    $content = preg_replace('/^.*?(<section.*<\/section>).*$/s', '$1', $content);
                    echo $content;
                }
                echo '</div>';
                break;
        }
        ?>
    </main>

    <footer class="bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 mt-16 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid gap-8 md:grid-cols-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Health &amp; Sanitation</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                        Empowering citizens with accessible health services, sanitation programs, and community updates in one place.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><a href="?page=home" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Dashboard</a></li>
                        <li><a href="?page=services" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Services</a></li>
                        <li><a href="?page=about" class="hover:text-primary dark:hover:text-blue-400 transition-colors">About</a></li>
                        <li><a href="?page=contact" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Contact</a></li>
                        <li><a href="?page=profile" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Profile</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Support</h3>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 12.414a2 2 0 010-2.828l4.243-4.243m1.414 1.414a2 2 0 010 2.828L16.243 15.9M7 7v10"/></svg><span>Hotline: (02) 123-4567</span></li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path></svg><span>Office Hours: Mon–Fri, 8AM–5PM</span></li>
                        <li class="flex items-center gap-2"><svg class="w-4 h-4 text-primary dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5 9 6.343 9 8s1.343 3 3 3zm0 2c-2.67 0-8 1.337-8 4v1h16v-1c0-2.663-5.33-4-8-4z"/></svg><span>Grace Park East, Caloocan City</span></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 mt-10 pt-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Health &amp; Sanitation Services. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const htmlRoot = document.getElementById('html-root');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        const storedTheme = localStorage.getItem('theme');
        const currentTheme = storedTheme || (htmlRoot.classList.contains('dark') ? 'dark' : 'light');
        htmlRoot.classList.remove('light', 'dark');
        htmlRoot.classList.add(currentTheme);

        if (currentTheme === 'dark') {
            darkIcon.classList.remove('hidden');
            lightIcon.classList.add('hidden');
        } else {
            darkIcon.classList.add('hidden');
            lightIcon.classList.remove('hidden');
        }

        themeToggle.addEventListener('click', function() {
            if (htmlRoot.classList.contains('dark')) {
                htmlRoot.classList.remove('dark');
                htmlRoot.classList.add('light');
                localStorage.setItem('theme', 'light');
                darkIcon.classList.add('hidden');
                lightIcon.classList.remove('hidden');
            } else {
                htmlRoot.classList.remove('light');
                htmlRoot.classList.add('dark');
                localStorage.setItem('theme', 'dark');
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
            }
        });

        // User dropdown functionality
        const userMenuButton = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('user-dropdown');

        userMenuButton.addEventListener('click', function() {
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Service category dropdown functionality & smooth navigation
        const serviceCategorySelect = document.getElementById('service-category');
        const serviceSections = document.querySelectorAll('.citizen-portal-content section[id]');
        const serviceLinks = document.querySelectorAll('a[data-service-target]');

        function updateServiceUrl(target) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', 'services');
            if (!target || target === 'all') {
                url.searchParams.delete('focus');
            } else {
                url.searchParams.set('focus', target);
            }
            window.history.replaceState({}, '', url.pathname + '?' + url.searchParams.toString());
        }

        function smoothScrollToSection(section) {
            if (!section) return;
            const nav = document.querySelector('nav');
            const offset = nav ? nav.offsetHeight + 16 : 100;
            const elementTop = section.getBoundingClientRect().top + window.pageYOffset - offset;
            window.scrollTo({
                top: elementTop < 0 ? 0 : elementTop,
                behavior: 'smooth'
            });
        }

        function applyServiceFilter(target, options = {}) {
            const { scroll = true, updateUrl = false } = options;
            let visibleSection = null;

            serviceSections.forEach(section => {
                if (!target || target === 'all') {
                    section.style.display = '';
                    return;
                }

                if (section.id === target) {
                    section.style.display = '';
                    visibleSection = section;
                } else {
                    section.style.display = 'none';
                }
            });

            if (updateUrl) {
                updateServiceUrl(target);
            }

            if (!scroll) return;

            if (!target || target === 'all') {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                smoothScrollToSection(visibleSection || document.getElementById(target));
            }
        }

        if (serviceCategorySelect) {
            serviceCategorySelect.addEventListener('change', function(event) {
                const selectedCategory = event.target.value || 'all';
                applyServiceFilter(selectedCategory, { scroll: true, updateUrl: true });
            });

            serviceLinks.forEach(link => {
                link.addEventListener('click', function(event) {
                    const target = this.dataset.serviceTarget;
                    if (!target) return;
                    event.preventDefault();
                    serviceCategorySelect.value = target;
                    applyServiceFilter(target, { scroll: true, updateUrl: true });
                });
            });

            const focusParam = new URLSearchParams(window.location.search).get('focus');
            if (focusParam && document.getElementById(focusParam)) {
                serviceCategorySelect.value = focusParam;
                applyServiceFilter(focusParam, { scroll: true, updateUrl: false });
            } else {
                applyServiceFilter('all', { scroll: false, updateUrl: false });
            }
        }
    </script>

    <!-- Scroll to Top Button -->
    <button id="scroll-to-top" class="fixed bottom-20 right-4 bg-primary dark:bg-blue-700 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-300 opacity-0 invisible">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
        </svg>
    </button>

    <!-- Chatbot -->
    <div id="chatbot-container" class="fixed bottom-4 right-4 z-50">
        <button id="chatbot-toggle" class="bg-primary dark:bg-blue-700 text-white p-4 rounded-full chatbot-shadow hover:bg-blue-700 dark:hover:bg-blue-800 transition-all duration-300">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
        </button>

        <div id="chatbot-window" class="hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg chatbot-shadow w-80 h-[28rem] flex flex-col transition-colors duration-300">
            <div class="bg-primary dark:bg-blue-700 text-white p-4 rounded-t-lg flex justify-between items-center">
                <h3 class="font-semibold">Health Assistant</h3>
                <div class="flex space-x-2">
                    <button id="chatbot-minimize" class="hover:bg-blue-700 dark:hover:bg-blue-800 p-1 rounded transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <button id="chatbot-close" class="hover:bg-blue-700 dark:hover:bg-blue-800 p-1 rounded transition-colors duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div id="chat-messages" class="flex-1 p-4 pb-2 overflow-y-auto space-y-4 bg-gray-50 dark:bg-gray-900 transition-colors duration-300 relative">
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded-lg transition-colors duration-300">
                    <p class="text-sm text-gray-800 dark:text-gray-200">Hello! I'm your Health Assistant. How can I help you today?</p>
                </div>

                <div id="quick-replies" class="space-y-2 sticky bottom-0 bg-gray-50 dark:bg-gray-900 p-2 rounded-lg border-t border-gray-200 dark:border-gray-700">
                    <p class="text-xs text-gray-600 dark:text-gray-400 font-medium text-center">Quick Options:</p>
                    <div class="grid grid-cols-2 gap-1">
                        <button class="quick-reply-btn bg-blue-100 dark:bg-blue-900 hover:bg-blue-200 dark:hover:bg-blue-800 text-blue-800 dark:text-blue-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="services" data-message="Tell me about your services">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Services
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-green-50 dark:bg-green-900 hover:bg-accent hover:text-white dark:hover:bg-green-800 text-green-primary dark:text-green-200 p-1.5 rounded text-left text-xs transition-colors duration-200 border border-accent" data-reply="contact" data-message="How can I contact you?">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-accent dark:text-green-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Contact
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-purple-100 dark:bg-purple-900 hover:bg-purple-200 dark:hover.bg-purple-800 text-purple-800 dark:text-purple-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="location" data-message="Where are you located?">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Location
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-orange-100 dark:bg-orange-900 hover:bg-orange-200 dark:hover:bg-orange-800 text-orange-800 dark:text-orange-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="hours" data-message="What are your operating hours?">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Hours
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-red-100 dark:bg-red-900 hover:bg-red-200 dark:hover:bg-red-800 text-red-800 dark:text-red-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="emergency" data-message="Do you have emergency services?">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                Emergency
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-indigo-100 dark:bg-indigo-900 hover:bg-indigo-200 dark:hover:bg-indigo-800 text-indigo-800 dark:text-indigo-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="appointment" data-message="How do I schedule an appointment?">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Appointment
                            </div>
                        </button>

                        <button class="quick-reply-btn bg-teal-100 dark:bg-teal-900 hover:bg-teal-200 dark:hover:bg-teal-800 text-teal-800 dark:text-teal-200 p-1.5 rounded text-left text-xs transition-colors duration-200" data-reply="permits" data-message="Tell me about permits and inspections">
                            <div class="flex items-center">
                                <svg class="w-3 h-3 mr-1 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Permits
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-lg transition-colors duration-300">
                <div class="flex justify-center mb-2">
                    <button id="chat-scroll-down" class="hidden bg-primary dark:bg-blue-700 hover:bg-blue-700 dark:hover:bg-blue-800 text-white p-2 rounded-full shadow-lg transition-all duration-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                        </svg>
                    </button>
                </div>

                <div class="flex space-x-2">
                    <input type="text" id="chat-input" placeholder="Type your message..." class="flex-1 p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200">
                    <button id="chat-send" class="bg-primary dark:bg-blue-700 text-white px-4 py-2 rounded-md hover:bg-blue-700 dark:hover:bg-blue-800 transition-colors duration-200">
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="../script.js"></script>
</body>
</html>