<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blood_donation_system');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper Functions
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

function check_login($required_role = null) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }

    if ($required_role && $_SESSION['role'] !== $required_role) {
        header("Location: unauthorized.php");
        exit();
    }
}

function get_user_role() {
    return $_SESSION['role'] ?? null;
}

function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

function redirect_to_dashboard() {
    $role = get_user_role();
    switch ($role) {
        case 'admin':
            header("Location: admin_dashboard.php");
            break;
        case 'donor':
            header("Location: donor_dashboard.php");
            break;
        case 'hospital':
            header("Location: hospital_dashboard.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}

function log_action($user_id, $action, $description = '', $ip_address = null) {
    global $conn;

    if ($ip_address === null) {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    $stmt = $conn->prepare("INSERT INTO system_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
    $stmt->execute();
    $stmt->close();
}

function create_notification($user_id, $title, $message, $type = 'info') {
    global $conn;

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $title, $message, $type);
    $stmt->execute();
    $stmt->close();
}

function get_blood_inventory() {
    global $conn;

    $result = $conn->query("SELECT * FROM blood_inventory ORDER BY blood_type");
    $inventory = [];

    while ($row = $result->fetch_assoc()) {
        $inventory[$row['blood_type']] = $row['quantity_ml'];
    }

    return $inventory;
}

function update_blood_inventory($blood_type, $quantity_change) {
    global $conn;

    $stmt = $conn->prepare("UPDATE blood_inventory SET quantity_ml = quantity_ml + ? WHERE blood_type = ?");
    $stmt->bind_param("is", $quantity_change, $blood_type);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function format_date($date) {
    return date('M d, Y', strtotime($date));
}

function format_datetime($datetime) {
    return date('M d, Y h:i A', strtotime($datetime));
}

function calculate_donor_eligibility($last_donation_date) {
    if (!$last_donation_date) {
        return true;
    }

    $last_donation = strtotime($last_donation_date);
    $today = time();
    $days_since_donation = ($today - $last_donation) / (60 * 60 * 24);

    // Donors must wait at least 56 days (8 weeks) between donations
    return $days_since_donation >= 56;
}

function get_urgency_color($urgency) {
    switch ($urgency) {
        case 'critical':
            return '#dc3545';
        case 'high':
            return '#fd7e14';
        case 'medium':
            return '#ffc107';
        case 'low':
            return '#28a745';
        default:
            return '#6c757d';
    }
}

// Default admin passkey (Change this in production!)
define('ADMIN_PASSKEY', 'admin123');
?>
