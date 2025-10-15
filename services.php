<?php 
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && isset($_SESSION['role']);

// Redirect logged-in users to their appropriate dashboard (only if no output yet)
if ($isLoggedIn && !headers_sent()) {
    $redirect_url = Database::getRoleRedirect($_SESSION['role']);
    header('Location: ' . $redirect_url);
    exit();
}

// Determine verification status (used when this file is embedded in citizen portal)
$isVerified = false;
if ($isLoggedIn) {
    $__u = $database->getUserById($_SESSION['user_id']);
    $isVerified = $__u && (($__u['verification_status'] ?? '') === 'verified');
}

// Now include header after session is started
include 'header.php'; 
?>

    <!-- Main Content -->
    <main class="pt-16">
        <!-- Detailed Sections -->
        <section id="health-center-details" class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white" data-translate="healthCenterTitle">Health Center Services</h2>
                <div class="grid md:grid-cols-3 gap-8 mb-12">
                    <div class="service-card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-blue-600 dark:text-blue-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white" data-translate="medicalConsultations">Medical Consultations</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4" data-translate="medicalConsultationsDesc">Professional medical consultations with qualified healthcare providers for general health concerns, routine check-ups, and specialized medical advice.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• <span data-translate="medicalConsultationsList1">General practice consultations</span></li>
                            <li>• <span data-translate="medicalConsultationsList2">Specialist referrals</span></li>
                            <li>• <span data-translate="medicalConsultationsList3">Health assessments</span></li>
                            <li>• <span data-translate="medicalConsultationsList4">Medical certificates</span></li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('medical-consultation')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Consultation
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="index.html" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-red-600 dark:text-red-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white" data-translate="emergencyCare">Emergency Care</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4" data-translate="emergencyCareDesc">24/7 emergency medical services for urgent health situations, accidents, and critical care needs with trained emergency response teams.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• <span data-translate="emergencyCareList1">24/7 emergency response</span></li>
                            <li>• <span data-translate="emergencyCareList2">First aid and trauma care</span></li>
                            <li>• <span data-translate="emergencyCareList3">Ambulance services</span></li>
                            <li>• <span data-translate="emergencyCareList4">Critical care stabilization</span></li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('emergency-care')" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Emergency Care
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg border-l-4 border-accent border-t border-r border-b border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-accent dark:text-green-light mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-green-primary dark:text-white" data-translate="preventiveCare">Preventive Care</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4" data-translate="preventiveCareDesc">Regular health screenings, preventive care programs, and wellness initiatives to maintain optimal health and prevent diseases.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• <span data-translate="preventiveCareList1">Annual health screenings</span></li>
                            <li>• <span data-translate="preventiveCareList2">Vaccination programs</span></li>
                            <li>• <span data-translate="preventiveCareList3">Health education workshops</span></li>
                            <li>• <span data-translate="preventiveCareList4">Wellness monitoring</span></li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('preventive-care')" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Schedule Preventive Care
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sanitation Permit & Inspection Services -->
        <section id="sanitation-permit" class="py-16 bg-custom-bg dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white" data-translate="sanitationPermitTitle">Sanitation Permit & Inspection Services</h2>
                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <div class="service-card bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300">
                        <div class="text-blue-600 dark:text-blue-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white" data-translate="businessPermits">Business Permits</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4" data-translate="businessPermitsDesc">Comprehensive sanitation permit processing for businesses, restaurants, food establishments, and commercial facilities to ensure compliance with health standards.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• <span data-translate="businessPermitsList1">New business permit applications</span></li>
                            <li>• <span data-translate="businessPermitsList2">Permit renewals and updates</span></li>
                            <li>• <span data-translate="businessPermitsList3">Food service establishment permits</span></li>
                            <li>• <span data-translate="businessPermitsList4">Commercial facility certifications</span></li>
                            <li>• <span data-translate="businessPermitsList5">Compliance documentation</span></li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('business-permit')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Apply for Business Permit
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-700 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300">
                        <div class="text-orange-600 dark:text-orange-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white" data-translate="healthInspectionsTitle">Health Inspections</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4" data-translate="healthInspectionsDesc">Professional health and sanitation inspections to ensure facilities meet safety standards and regulatory requirements for public health protection.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• <span data-translate="healthInspectionsList1">Routine facility inspections</span></li>
                            <li>• <span data-translate="healthInspectionsList2">Food safety assessments</span></li>
                            <li>• <span data-translate="healthInspectionsList3">Water quality testing</span></li>
                            <li>• <span data-translate="healthInspectionsList4">Waste management evaluation</span></li>
                            <li>• <span data-translate="healthInspectionsList5">Compliance reporting</span></li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('health-inspection')" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Health Inspection
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Immunization & Nutrition Services -->
        <section id="immunization" class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white" data-translate="immunizationTitle">Immunization & Nutrition Tracker</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="service-card bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-purple-600 dark:text-purple-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Vaccination Programs</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Comprehensive immunization services following national vaccination schedules for children, adults, and special populations with digital tracking systems.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• Childhood immunization schedules</li>
                            <li>• Adult vaccination programs</li>
                            <li>• Travel vaccination services</li>
                            <li>• Digital immunization records</li>
                            <li>• Vaccine inventory management</li>
                            <li>• Adverse event monitoring</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('vaccination')" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Schedule Vaccination
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border-l-4 border-accent border-t border-r border-b border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-accent dark:text-green-light mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-green-primary dark:text-white">Nutrition Monitoring</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Advanced nutrition tracking and monitoring services to assess nutritional status, provide dietary guidance, and prevent malnutrition in the community.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• Nutritional status assessments</li>
                            <li>• Growth monitoring for children</li>
                            <li>• Dietary counseling services</li>
                            <li>• Malnutrition prevention programs</li>
                            <li>• BMI and health metrics tracking</li>
                            <li>• Community nutrition education</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('nutrition-monitoring')" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Nutrition Assessment
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Wastewater & Septic Services -->
        <section id="wastewater" class="py-16 bg-custom-bg dark:bg-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Wastewater & Septic Services</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="service-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300">
                        <div class="text-blue-600 dark:text-blue-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">System Inspections</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Professional inspections of septic systems, wastewater treatment facilities, and drainage systems to ensure proper functioning.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• Septic tank inspections</li>
                            <li>• Drainage system checks</li>
                            <li>• Compliance assessments</li>
                            <li>• Environmental impact evaluation</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('system-inspection')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request System Inspection
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300">
                        <div class="text-orange-600 dark:text-orange-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Maintenance Services</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Regular maintenance and repair services for wastewater systems to prevent failures and ensure optimal performance.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• Routine system maintenance</li>
                            <li>• Emergency repairs</li>
                            <li>• Pump station servicing</li>
                            <li>• Preventive maintenance programs</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('maintenance-service')" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Maintenance Service
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-700 p-6 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 transition-all duration-300">
                        <div class="text-green-600 dark:text-green-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Installation & Upgrades</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Professional installation of new wastewater systems and upgrades to existing infrastructure for improved efficiency.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-1 mb-6">
                            <li>• New system installations</li>
                            <li>• System upgrades and modernization</li>
                            <li>• Technology integration</li>
                            <li>• Capacity expansion services</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('installation-upgrade')" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Installation/Upgrade
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Health Surveillance System -->
        <section id="surveillance" class="py-16 bg-white dark:bg-gray-900">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12 text-gray-900 dark:text-white">Health Surveillance System</h2>
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="service-card bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-red-600 dark:text-red-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Disease Monitoring</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Advanced surveillance systems for tracking disease outbreaks, monitoring health trends, and implementing early warning systems for public health protection.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• Real-time disease tracking</li>
                            <li>• Outbreak investigation</li>
                            <li>• Epidemiological analysis</li>
                            <li>• Community health reporting</li>
                            <li>• Early warning systems</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('disease-monitoring')" class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Report Disease/Health Concern
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="service-card bg-white dark:bg-gray-800 p-8 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 transition-all duration-300">
                        <div class="text-yellow-600 dark:text-yellow-400 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">Environmental Monitoring</h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4">Comprehensive environmental health surveillance including air quality, water safety, and hazardous material monitoring to protect community health.</p>
                        <ul class="text-sm text-gray-500 dark:text-gray-400 space-y-2 mb-6">
                            <li>• Air quality monitoring</li>
                            <li>• Water safety assessments</li>
                            <li>• Environmental hazard detection</li>
                            <li>• Pollution control measures</li>
                            <li>• Public health alerts</li>
                        </ul>
                        <?php if ($isLoggedIn && $isVerified): ?>
                        <button onclick="openServiceForm('environmental-monitoring')" class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            Request Environmental Assessment
                        </button>
                        <?php elseif ($isLoggedIn): ?>
                        <a href="?page=profile" class="w-full bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Verify your account to request this service
                        </a>
                        <?php else: ?>
                        <a href="login.php" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 block text-center">
                            Login to Request Service
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Service Request Modal -->
        <div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 id="modalTitle" class="text-2xl font-bold text-gray-900 dark:text-white"></h2>
                        <button onclick="closeServiceForm()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <form id="serviceForm" class="space-y-6">
                        <input type="hidden" id="serviceType" name="service_type">
                        
                        <!-- Personal Information -->
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name *</label>
                                <input type="text" name="full_name" required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email *</label>
                                <input type="email" name="email" required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Phone Number *</label>
                                <input type="tel" name="phone" required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preferred Date</label>
                                <input type="date" name="preferred_date" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Address *</label>
                            <textarea name="address" rows="2" required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                        
                        <!-- Dynamic Fields Container -->
                        <div id="dynamicFields"></div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Service Details *</label>
                            <textarea name="service_details" rows="4" placeholder="Please provide detailed information about your service request..." required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Urgency Level</label>
                            <select name="urgency" class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Verification *</label>
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <canvas id="captchaCanvas" width="180" height="60" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700"></canvas>
                                    <button type="button" id="captchaReload" class="px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200">Refresh</button>
                                </div>
                                <input type="text" id="captchaInput" name="captcha_input" maxlength="6" autocomplete="off" placeholder="ENTER THE LETTERS SHOWN ABOVE" required class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white uppercase tracking-widest">
                                <p id="captchaError" class="text-sm text-red-600 dark:text-red-400 hidden">Incorrect verification code. Please try again.</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-4 pt-4">
                            <button type="button" onclick="closeServiceForm()" class="flex-1 px-6 py-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                Cancel
                            </button>
                            <button type="submit" class="flex-1 px-6 py-3 bg-primary hover:bg-blue-700 text-white rounded-lg">
                                Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        const serviceConfig = {
            'medical-consultation': {
                title: 'Medical Consultation Request',
                fields: [
                    { type: 'select', name: 'consultation_type', label: 'Type of Consultation', options: ['General Practice', 'Specialist Referral', 'Health Assessment', 'Medical Certificate'], required: true },
                    { type: 'select', name: 'consultation_urgency', label: 'Urgency Level', options: ['Routine', 'Urgent', 'Emergency'], required: true }
                ]
            },
            'emergency-care': {
                title: 'Emergency Care Request',
                fields: [
                    { type: 'select', name: 'emergency_type', label: 'Type of Emergency', options: ['Medical Emergency', 'Accident', 'Trauma', 'Critical Care'], required: true },
                    { type: 'text', name: 'symptoms', label: 'Symptoms/Condition', required: true }
                ]
            },
            'preventive-care': {
                title: 'Preventive Care Appointment',
                fields: [
                    { type: 'select', name: 'preventive_service_type', label: 'Service Type', options: ['Health Screening', 'Vaccination', 'Health Education', 'Wellness Check'], required: true },
                    { type: 'number', name: 'age', label: 'Age', required: true }
                ]
            },
            'business-permit': {
                title: 'Business Permit Application',
                fields: [
                    { type: 'text', name: 'business_name', label: 'Business Name', required: true },
                    { type: 'select', name: 'business_type', label: 'Business Type', options: ['Restaurant', 'Food Service', 'Commercial Facility', 'Other'], required: true },
                    { type: 'select', name: 'permit_type', label: 'Permit Type', options: ['New Application', 'Renewal', 'Update'], required: true }
                ]
            },
            'health-inspection': {
                title: 'Health Inspection Request',
                fields: [
                    { type: 'select', name: 'inspection_type', label: 'Inspection Type', options: ['Routine Facility', 'Food Safety', 'Water Quality', 'Waste Management'], required: true },
                    { type: 'text', name: 'facility_name', label: 'Facility Name', required: true }
                ]
            },
            'vaccination': {
                title: 'Vaccination Appointment',
                fields: [
                    { type: 'select', name: 'vaccine_type', label: 'Vaccine Type', options: ['Childhood Immunization', 'Adult Vaccination', 'Travel Vaccination', 'COVID-19'], required: true },
                    { type: 'number', name: 'age', label: 'Age', required: true }
                ]
            },
            'nutrition-monitoring': {
                title: 'Nutrition Assessment Request',
                fields: [
                    { type: 'select', name: 'assessment_type', label: 'Assessment Type', options: ['Nutritional Status', 'Growth Monitoring', 'Dietary Counseling', 'BMI Check'], required: true },
                    { type: 'number', name: 'age', label: 'Age', required: true }
                ]
            },
            'system-inspection': {
                title: 'System Inspection Request',
                fields: [
                    { type: 'select', name: 'system_type', label: 'System Type', options: ['Septic Tank', 'Drainage System', 'Wastewater Treatment'], required: true },
                    { type: 'text', name: 'property_address', label: 'Property Address', required: true }
                ]
            },
            'maintenance-service': {
                title: 'Maintenance Service Request',
                fields: [
                    { type: 'select', name: 'service_type', label: 'Service Type', options: ['Routine Maintenance', 'Emergency Repair', 'Pump Servicing'], required: true },
                    { type: 'select', name: 'urgency', label: 'Urgency', options: ['Routine', 'Urgent', 'Emergency'], required: true }
                ]
            },
            'installation-upgrade': {
                title: 'Installation/Upgrade Request',
                fields: [
                    { type: 'select', name: 'service_type', label: 'Service Type', options: ['New Installation', 'System Upgrade', 'Technology Integration', 'Capacity Expansion'], required: true },
                    { type: 'text', name: 'property_address', label: 'Property Address', required: true }
                ]
            },
            'disease-monitoring': {
                title: 'Disease/Health Concern Report',
                fields: [
                    { type: 'select', name: 'concern_type', label: 'Type of Concern', options: ['Disease Outbreak', 'Health Trend', 'Environmental Health Risk'], required: true },
                    { type: 'text', name: 'symptoms', label: 'Symptoms/Description', required: true }
                ]
            },
            'environmental-monitoring': {
                title: 'Environmental Assessment Request',
                fields: [
                    { type: 'select', name: 'assessment_type', label: 'Assessment Type', options: ['Air Quality', 'Water Safety', 'Environmental Hazard', 'Pollution Control'], required: true },
                    { type: 'text', name: 'location', label: 'Location/Address', required: true }
                ]
            }
        };

        const serviceModal = document.getElementById('serviceModal');
        const serviceForm = document.getElementById('serviceForm');
        const captchaCanvas = document.getElementById('captchaCanvas');
        const captchaInput = document.getElementById('captchaInput');
        const captchaError = document.getElementById('captchaError');
        const captchaReload = document.getElementById('captchaReload');
        const modalTitle = document.getElementById('modalTitle');
        const serviceTypeInput = document.getElementById('serviceType');

        let captchaValue = '';

        function generateCaptcha() {
            if (!captchaCanvas) return;

            const ctx = captchaCanvas.getContext('2d');
            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            captchaValue = Array.from({ length: 6 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, captchaCanvas.width, captchaCanvas.height);

            const gradient = ctx.createLinearGradient(0, 0, captchaCanvas.width, captchaCanvas.height);
            gradient.addColorStop(0, '#1d4ed8');
            gradient.addColorStop(1, '#fb923c');
            ctx.fillStyle = '#f8fafc';
            ctx.fillRect(0, 0, captchaCanvas.width, captchaCanvas.height);

            ctx.font = '32px "Courier New", monospace';
            ctx.setTransform(1, 0, Math.random() * 0.3 - 0.15, 1, 0, 10);
            ctx.fillStyle = gradient;
            ctx.fillText(captchaValue, 18, 42);

            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.strokeStyle = 'rgba(30, 64, 175, 0.35)';
            ctx.beginPath();
            for (let i = 0; i < 3; i++) {
                ctx.moveTo(Math.random() * captchaCanvas.width, Math.random() * captchaCanvas.height);
                ctx.bezierCurveTo(
                    Math.random() * captchaCanvas.width,
                    Math.random() * captchaCanvas.height,
                    Math.random() * captchaCanvas.width,
                    Math.random() * captchaCanvas.height,
                    Math.random() * captchaCanvas.width,
                    Math.random() * captchaCanvas.height
                );
            }
            ctx.stroke();
        }

        function openServiceForm(serviceType) {
            const config = serviceConfig[serviceType];
            if (!config) return;

            if (!serviceModal || !serviceForm || !modalTitle || !serviceTypeInput) {
                console.warn('Service modal elements missing. Cannot open service form.');
                return;
            }

            serviceForm.reset();
            if (captchaError) {
                captchaError.classList.add('hidden');
            }
            if (captchaInput) {
                captchaInput.value = '';
            }

            modalTitle.textContent = config.title;
            serviceTypeInput.value = serviceType;
            
            // Generate dynamic fields
            const dynamicFields = document.getElementById('dynamicFields');
            dynamicFields.innerHTML = '';
            
            config.fields.forEach(field => {
                const div = document.createElement('div');
                div.className = 'mb-4';
                
                let fieldHTML = `<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">${field.label}${field.required ? ' *' : ''}</label>`;
                
                if (field.type === 'select') {
                    fieldHTML += `<select name="${field.name}" ${field.required ? 'required' : ''} class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">`;
                    fieldHTML += '<option value="">Select an option</option>';
                    field.options.forEach(option => {
                        fieldHTML += `<option value="${option}">${option}</option>`;
                    });
                    fieldHTML += '</select>';
                } else {
                    fieldHTML += `<input type="${field.type}" name="${field.name}" ${field.required ? 'required' : ''} class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">`;
                }
                
                div.innerHTML = fieldHTML;
                dynamicFields.appendChild(div);
            });
            
            serviceModal.classList.remove('hidden');
            generateCaptcha();
        }

        function closeServiceForm() {
            if (!serviceModal || !serviceForm) return;
            serviceModal.classList.add('hidden');
            serviceForm.reset();
            if (captchaInput) {
                captchaInput.value = '';
            }
            if (captchaError) {
                captchaError.classList.add('hidden');
            }
        }

        if (captchaReload) {
            captchaReload.addEventListener('click', function() {
                generateCaptcha();
                captchaError?.classList.add('hidden');
                if (captchaInput) {
                    captchaInput.value = '';
                    captchaInput.focus();
                }
            });
        }

        if (serviceForm) {
            serviceForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (captchaInput && captchaError && captchaCanvas) {
                const captchaEntered = captchaInput.value.trim().toUpperCase();
                if (captchaEntered !== captchaValue) {
                    captchaError.classList.remove('hidden');
                    captchaInput.value = '';
                    captchaInput.focus();
                    generateCaptcha();
                    return;
                }
            }
             
            const formData = new FormData(this);
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            
            // Disable submit button and show loading
            submitButton.disabled = true;
            submitButton.textContent = 'Submitting...';
            
            // Convert FormData to JSON
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            try {
                // Resolve endpoint based on current path (works both on main site and inside citizen portal)
                const endpoint = window.location.pathname.includes('/citizen/')
                    ? '../process_service_request.php'
                    : 'process_service_request.php';

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                // If server returned HTML (e.g., login redirect), throw a readable error
                const text = await response.text();
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${text.slice(0, 120)}`);
                }
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                    throw new Error('Server returned HTML instead of JSON. Are you logged in?');
                }
                const result = JSON.parse(text);
                
                if (result.success) {
                    // Show success message
                    alert('✅ ' + result.message);
                    closeServiceForm();
                } else {
                    // Show error message
                    if (result.debug) {
                        console.error('Service submit debug:', result.debug);
                        alert('❌ ' + result.message + '\n\nDetails: ' + JSON.stringify(result.debug));
                    } else {
                        alert('❌ ' + result.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ An error occurred while submitting your request. Please try again.');
            } finally {
                // Re-enable submit button
                submitButton.disabled = false;
                submitButton.textContent = originalText;
                if (captchaInput) {
                    captchaInput.value = '';
                }
            }
        });
        }

        // Close modal when clicking outside
        if (serviceModal) {
            serviceModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeServiceForm();
                }
            });
        }
        </script>
    </main>

<?php include 'footer.php'; ?>
