<?php
// Automatic Database Setup Script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🩸 BloodLife Database Setup</h1>";
echo "<p>This will automatically create the database and all tables for you.</p>";
echo "<hr>";

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'blood_donation_system';

// Step 1: Connect to MySQL
echo "<h3>Step 1: Connecting to MySQL...</h3>";
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("❌ <strong>FAILED:</strong> Could not connect to MySQL: " . $conn->connect_error . "<br><br><strong>Solution:</strong> Make sure MySQL is running in XAMPP Control Panel!");
}
echo "✅ <strong>SUCCESS:</strong> Connected to MySQL<br><br>";

// Step 2: Create Database
echo "<h3>Step 2: Creating Database...</h3>";
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if ($conn->query($sql) === TRUE) {
    echo "✅ <strong>SUCCESS:</strong> Database '$db_name' created or already exists<br><br>";
} else {
    die("❌ <strong>FAILED:</strong> " . $conn->error);
}

// Step 3: Select Database
echo "<h3>Step 3: Selecting Database...</h3>";
if ($conn->select_db($db_name)) {
    echo "✅ <strong>SUCCESS:</strong> Database selected<br><br>";
} else {
    die("❌ <strong>FAILED:</strong> " . $conn->error);
}

// Step 4: Create Tables
echo "<h3>Step 4: Creating Tables...</h3>";

// Users Table
echo "Creating <strong>users</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'donor', 'hospital') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Admins Table
echo "Creating <strong>admins</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    passkey_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Donors Table
echo "Creating <strong>donors</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS donors (
    donor_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    phone VARCHAR(15) NOT NULL,
    street_address VARCHAR(200),
    city VARCHAR(50),
    pincode VARCHAR(10),
    last_donation_date DATE NULL,
    is_eligible BOOLEAN DEFAULT TRUE,
    total_donations INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_blood_type (blood_type),
    INDEX idx_city (city)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Hospitals Table
echo "Creating <strong>hospitals</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS hospitals (
    hospital_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    hospital_name VARCHAR(150) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    street_address VARCHAR(200),
    city VARCHAR(50),
    pincode VARCHAR(10),
    license_number VARCHAR(50) UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_hospital_name (hospital_name),
    INDEX idx_city (city)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Blood Inventory Table
echo "Creating <strong>blood_inventory</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS blood_inventory (
    inventory_id INT AUTO_INCREMENT PRIMARY KEY,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    quantity_ml INT DEFAULT 0,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_blood_type (blood_type)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Donations Table
echo "Creating <strong>donations</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS donations (
    donation_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    donation_date DATE NOT NULL,
    quantity_ml INT NOT NULL DEFAULT 450,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    hospital_id INT NULL,
    status ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL,
    INDEX idx_donor (donor_id),
    INDEX idx_donation_date (donation_date),
    INDEX idx_status (status)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Blood Requests Table
echo "Creating <strong>blood_requests</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS blood_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    quantity_ml INT NOT NULL,
    urgency ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    required_date DATE NOT NULL,
    status ENUM('pending', 'fulfilled', 'partial', 'cancelled') DEFAULT 'pending',
    fulfilled_quantity_ml INT DEFAULT 0,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE,
    INDEX idx_hospital (hospital_id),
    INDEX idx_status (status),
    INDEX idx_urgency (urgency),
    INDEX idx_blood_type (blood_type)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Appointments Table
echo "Creating <strong>appointments</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    donor_id INT NOT NULL,
    hospital_id INT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (donor_id) REFERENCES donors(donor_id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL,
    INDEX idx_donor (donor_id),
    INDEX idx_appointment_date (appointment_date),
    INDEX idx_status (status)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// Notifications Table
echo "Creating <strong>notifications</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'urgent', 'success') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

// System Logs Table
echo "Creating <strong>system_logs</strong> table... ";
$sql = "CREATE TABLE IF NOT EXISTS system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
)";
if ($conn->query($sql)) {
    echo "✅<br>";
} else {
    echo "❌ Error: " . $conn->error . "<br>";
}

echo "<br>";

// Step 5: Initialize Blood Inventory
echo "<h3>Step 5: Initializing Blood Inventory...</h3>";
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
foreach ($blood_types as $type) {
    $sql = "INSERT INTO blood_inventory (blood_type, quantity_ml) VALUES ('$type', 0) ON DUPLICATE KEY UPDATE blood_type=blood_type";
    if ($conn->query($sql)) {
        echo "✅ Blood type <strong>$type</strong> initialized<br>";
    }
}
echo "<br>";

// Step 6: Create Default Admin User
echo "<h3>Step 6: Creating Default Admin User...</h3>";

// Check if admin exists
$result = $conn->query("SELECT user_id FROM users WHERE username = 'admin'");
if ($result && $result->num_rows > 0) {
    echo "⚠️ Admin user already exists, skipping...<br><br>";
} else {
    $admin_username = 'admin';
    $admin_email = 'admin@bloodlife.com';
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $admin_role = 'admin';

    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $admin_username, $admin_email, $admin_password, $admin_role);

    if ($stmt->execute()) {
        $admin_user_id = $stmt->insert_id;
        echo "✅ Admin user created (ID: $admin_user_id)<br>";

        // Create admin record
        $passkey_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("INSERT INTO admins (user_id, passkey_hash, full_name) VALUES (?, ?, 'System Administrator')");
        $stmt2->bind_param("is", $admin_user_id, $passkey_hash);

        if ($stmt2->execute()) {
            echo "✅ Admin record created<br>";
        }
        $stmt2->close();
    } else {
        echo "❌ Failed to create admin user: " . $conn->error . "<br>";
    }
    $stmt->close();
}

echo "<br>";

// Final Summary
echo "<hr>";
echo "<h2>✅ Database Setup Complete!</h2>";

echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border: 1px solid #c3e6cb; margin: 20px 0;'>";
echo "<h3 style='color: #155724; margin-top: 0;'>Everything is ready! 🎉</h3>";
echo "<p style='color: #155724;'>";
echo "<strong>Database:</strong> $db_name<br>";
echo "<strong>Tables Created:</strong> 10<br>";
echo "<strong>Blood Types Initialized:</strong> 8<br>";
echo "<strong>Admin Account:</strong> Created<br>";
echo "</p>";
echo "</div>";

echo "<h3>Default Login Credentials:</h3>";
echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffeaa7;'>";
echo "<p><strong>Username:</strong> admin<br>";
echo "<strong>Password:</strong> admin123<br>";
echo "<strong>Admin Passkey:</strong> admin123</p>";
echo "</div>";

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='login.php'>Login as Admin</a></li>";
echo "<li><a href='register.php'>Register a Donor</a></li>";
echo "<li><a href='register.php'>Register a Hospital</a></li>";
echo "<li><a href='test_connection.php'>Verify Database</a></li>";
echo "</ol>";

echo "<div style='background: #d1ecf1; padding: 15px; border-radius: 8px; border: 1px solid #bee5eb; margin: 20px 0;'>";
echo "<p style='color: #0c5460;'><strong>Important:</strong> If you need to reset everything, just run this page again. It's safe to run multiple times.</p>";
echo "</div>";

$conn->close();
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f8f9fa;
        color: #333;
    }
    h1 {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        padding-bottom: 10px;
    }
    h2 {
        color: #28a745;
    }
    h3 {
        color: #495057;
        background: #e9ecef;
        padding: 10px;
        border-left: 4px solid #667eea;
    }
    a {
        color: #667eea;
        text-decoration: none;
        font-weight: bold;
    }
    a:hover {
        text-decoration: underline;
    }
    hr {
        border: none;
        border-top: 2px solid #dee2e6;
        margin: 30px 0;
    }
</style>
