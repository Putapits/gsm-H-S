<?php
// Debug flag (set to false in production)
$DEBUG = true;
// Start session and check login status BEFORE any output
require_once 'include/database.php';
startSecureSession();

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'You must be logged in to submit a service request.']);
    exit();
}

// Check verification status
if (!$database->isUserVerified($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account is not verified. Please upload a valid ID in your Profile and wait for admin approval before submitting service requests.']);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data.']);
    exit();
}

// Validate required fields
$required_fields = ['service_type', 'full_name', 'email', 'phone', 'address', 'service_details'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit();
    }
}

// Validate email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit();
}

// Prepare service data and merge dynamic fields into details
$base_fields = [
    'service_type', 'full_name', 'email', 'phone', 'address', 'service_details', 'preferred_date', 'urgency'
];

// Extract additional dynamic fields
$extra_pairs = [];
foreach ($input as $key => $value) {
    if (!in_array($key, $base_fields, true) && $value !== '' && $value !== null) {
        // Pretty label: convert snake_case or hyphen-case to Title Case
        $label = ucwords(str_replace(['_', '-'], ' ', $key));
        $extra_pairs[] = "$label: " . (is_array($value) ? implode(', ', $value) : trim((string)$value));
    }
}

$details = trim($input['service_details']);
if (!empty($extra_pairs)) {
    $details .= (strlen($details) ? "\n\n" : '') . "Additional Information:\n- " . implode("\n- ", $extra_pairs);
}

// Prepare final payload for DB
$serviceData = [
    'user_id' => $_SESSION['user_id'],
    'service_type' => trim($input['service_type']),
    'full_name' => trim($input['full_name']),
    'email' => trim($input['email']),
    'phone' => trim($input['phone']),
    'address' => trim($input['address']),
    'service_details' => $details,
    'preferred_date' => !empty($input['preferred_date']) ? $input['preferred_date'] : null,
    'urgency' => !empty($input['urgency']) ? $input['urgency'] : 'medium'
];

// Create service request
try {
    if ($database->createServiceRequest($serviceData)) {
        echo json_encode([
            'success' => true,
            'message' => 'Service request submitted successfully! We will contact you soon to discuss your request.'
        ]);
    } else {
        http_response_code(500);
        $resp = ['success' => false, 'message' => 'Failed to submit service request. Please try again.'];
        if ($DEBUG) {
            $resp['debug'] = [
                'service_type' => $serviceData['service_type'],
                'has_user' => isset($_SESSION['user_id']),
                'input_keys' => array_keys($input)
            ];
        }
        echo json_encode($resp);
    }
} catch (Exception $e) {
    error_log("Service request error: " . $e->getMessage());
    http_response_code(500);
    $resp = ['success' => false, 'message' => 'An error occurred. Please try again later.'];
    if ($DEBUG) {
        $resp['debug'] = ['exception' => $e->getMessage()];
    }
    echo json_encode($resp);
}
?>
