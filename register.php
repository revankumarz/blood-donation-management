<?php
require_once 'config.php';

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];
    $role = sanitize_input($_POST['role']);

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error_message = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long!";
    } else {
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username or email already exists!";
            $stmt->close();
        } else {
            $stmt->close();

            // Hash the password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert into users table
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password_hash, $role);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt->close();

                // Insert role-specific data
                if ($role === 'admin') {
                    $admin_passkey = $_POST['adminPasskey'];

                    // Verify admin passkey
                    if ($admin_passkey !== ADMIN_PASSKEY) {
                        // Delete the user if passkey is wrong
                        $conn->query("DELETE FROM users WHERE user_id = $user_id");
                        $error_message = "Invalid admin passkey!";
                    } else {
                        $passkey_hash = password_hash($admin_passkey, PASSWORD_DEFAULT);
                        $stmt = $conn->prepare("INSERT INTO admins (user_id, passkey_hash) VALUES (?, ?)");
                        $stmt->bind_param("is", $user_id, $passkey_hash);
                        $stmt->execute();
                        $stmt->close();

                        log_action($user_id, "ADMIN_REGISTERED", "New admin registered: $username");
                        $success_message = "Admin registration successful! Please login.";
                    }

                } elseif ($role === 'donor') {
                    $full_name = sanitize_input($_POST['donorName']);
                    $dob = sanitize_input($_POST['dob']);
                    $gender = sanitize_input($_POST['gender']);
                    $blood_type = sanitize_input($_POST['bloodType']);
                    $phone = sanitize_input($_POST['donorPhone']);
                    $street = sanitize_input($_POST['donorStreet']);
                    $city = sanitize_input($_POST['donorCity']);
                    $pincode = sanitize_input($_POST['donorPincode']);

                    $stmt = $conn->prepare("INSERT INTO donors (user_id, full_name, date_of_birth, gender, blood_type, phone, street_address, city, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssssss", $user_id, $full_name, $dob, $gender, $blood_type, $phone, $street, $city, $pincode);
                    $stmt->execute();
                    $stmt->close();

                    log_action($user_id, "DONOR_REGISTERED", "New donor registered: $full_name ($blood_type)");
                    create_notification($user_id, "Welcome to BloodLife!", "Thank you for registering as a blood donor. Your contribution can save lives!", "success");
                    $success_message = "Donor registration successful! Please login.";

                } elseif ($role === 'hospital') {
                    $hospital_name = sanitize_input($_POST['hospitalName']);
                    $phone = sanitize_input($_POST['hospitalPhone']);
                    $street = sanitize_input($_POST['hospitalStreet']);
                    $city = sanitize_input($_POST['hospitalCity']);
                    $pincode = sanitize_input($_POST['hospitalPincode']);

                    $stmt = $conn->prepare("INSERT INTO hospitals (user_id, hospital_name, phone, street_address, city, pincode) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("isssss", $user_id, $hospital_name, $phone, $street, $city, $pincode);
                    $stmt->execute();
                    $stmt->close();

                    log_action($user_id, "HOSPITAL_REGISTERED", "New hospital registered: $hospital_name");
                    create_notification($user_id, "Welcome to BloodLife!", "Your hospital has been successfully registered. You can now request blood.", "success");
                    $success_message = "Hospital registration successful! Please login.";
                }
            } else {
                $error_message = "Registration failed. Please try again.";
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BloodLife</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <div class="form-card">
            <a href="index.html" class="home-link">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife</span>
            </a>
            <h1>Register</h1>
            <p class="subtitle">Join Our Community</p>

            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                    <br><a href="login.php" style="color: #28a745; font-weight: bold;">Click here to login</a>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="">
                <!-- Common User Fields -->
                <div class="form-group">
                    <label for="role">Register as</label>
                    <select id="role" name="role" required onchange="toggleRoleFields()">
                        <option value="">Select Role</option>
                        <option value="admin">Admin</option>
                        <option value="donor">Donor</option>
                        <option value="hospital">Hospital</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required placeholder="Choose a username">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required placeholder="Create a password" minlength="6">
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm your password">
                </div>

                <!-- Admin Specific Fields -->
                <div id="adminFields" class="role-fields" style="display: none;">
                    <h3>Admin Verification</h3>

                    <div class="form-group">
                        <label for="adminPasskey">Passkey</label>
                        <input type="password" id="adminPasskey" name="adminPasskey" placeholder="Enter the passkey">
                        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">This passkey is provided only to authorized administrators</small>
                    </div>
                </div>

                <!-- Donor Specific Fields -->
                <div id="donorFields" class="role-fields" style="display: none;">
                    <h3>Donor Information</h3>

                    <div class="form-group">
                        <label for="donorName">Full Name</label>
                        <input type="text" id="donorName" name="donorName" placeholder="Enter your full name">
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth</label>
                        <input type="date" id="dob" name="dob">
                    </div>

                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bloodType">Blood Type</label>
                        <select id="bloodType" name="bloodType">
                            <option value="">Select Blood Type</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="donorPhone">Phone Number</label>
                        <input type="tel" id="donorPhone" name="donorPhone" placeholder="Enter your phone number">
                    </div>

                    <div class="form-group">
                        <label for="donorStreet">Street Address</label>
                        <input type="text" id="donorStreet" name="donorStreet" placeholder="Street address">
                    </div>

                    <div class="form-group">
                        <label for="donorCity">City</label>
                        <input type="text" id="donorCity" name="donorCity" placeholder="City">
                    </div>

                    <div class="form-group">
                        <label for="donorPincode">Pincode</label>
                        <input type="text" id="donorPincode" name="donorPincode" placeholder="Pincode">
                    </div>
                </div>

                <!-- Hospital Specific Fields -->
                <div id="hospitalFields" class="role-fields" style="display: none;">
                    <h3>Hospital Information</h3>

                    <div class="form-group">
                        <label for="hospitalName">Hospital Name</label>
                        <input type="text" id="hospitalName" name="hospitalName" placeholder="Enter hospital name">
                    </div>

                    <div class="form-group">
                        <label for="hospitalPhone">Phone Number</label>
                        <input type="tel" id="hospitalPhone" name="hospitalPhone" placeholder="Hospital phone number">
                    </div>

                    <div class="form-group">
                        <label for="hospitalStreet">Street Address</label>
                        <input type="text" id="hospitalStreet" name="hospitalStreet" placeholder="Street address">
                    </div>

                    <div class="form-group">
                        <label for="hospitalCity">City</label>
                        <input type="text" id="hospitalCity" name="hospitalCity" placeholder="City">
                    </div>

                    <div class="form-group">
                        <label for="hospitalPincode">Pincode</label>
                        <input type="text" id="hospitalPincode" name="hospitalPincode" placeholder="Pincode">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="form-footer">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>

    <script>
        function toggleRoleFields() {
            const role = document.getElementById('role').value;
            const adminFields = document.getElementById('adminFields');
            const donorFields = document.getElementById('donorFields');
            const hospitalFields = document.getElementById('hospitalFields');

            if (role === 'admin') {
                adminFields.style.display = 'block';
                donorFields.style.display = 'none';
                hospitalFields.style.display = 'none';
                setFieldsRequired('adminFields', true);
                setFieldsRequired('donorFields', false);
                setFieldsRequired('hospitalFields', false);
            } else if (role === 'donor') {
                adminFields.style.display = 'none';
                donorFields.style.display = 'block';
                hospitalFields.style.display = 'none';
                setFieldsRequired('adminFields', false);
                setFieldsRequired('donorFields', true);
                setFieldsRequired('hospitalFields', false);
            } else if (role === 'hospital') {
                adminFields.style.display = 'none';
                donorFields.style.display = 'none';
                hospitalFields.style.display = 'block';
                setFieldsRequired('adminFields', false);
                setFieldsRequired('donorFields', false);
                setFieldsRequired('hospitalFields', true);
            } else {
                adminFields.style.display = 'none';
                donorFields.style.display = 'none';
                hospitalFields.style.display = 'none';
                setFieldsRequired('adminFields', false);
                setFieldsRequired('donorFields', false);
                setFieldsRequired('hospitalFields', false);
            }
        }

        function setFieldsRequired(fieldsetId, required) {
            const fieldset = document.getElementById(fieldsetId);
            const inputs = fieldset.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (required) {
                    input.setAttribute('required', 'required');
                } else {
                    input.removeAttribute('required');
                }
            });
        }

        // Client-side password validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>

    <style>
        .alert {
            padding: 12px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</body>
</html>
