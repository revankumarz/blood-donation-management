<?php
require_once 'config.php';
check_login('donor');

$user_id = get_user_id();

// Get donor information
$stmt = $conn->prepare("SELECT d.*, u.username, u.email, u.created_at FROM donors d JOIN users u ON d.user_id = u.user_id WHERE d.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = sanitize_input($_POST['phone']);
    $street = sanitize_input($_POST['street']);
    $city = sanitize_input($_POST['city']);
    $pincode = sanitize_input($_POST['pincode']);

    $stmt = $conn->prepare("UPDATE donors SET phone = ?, street_address = ?, city = ?, pincode = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $phone, $street, $city, $pincode, $user_id);

    if ($stmt->execute()) {
        header("Location: donor_profile.php?success=updated");
        exit();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Donor</span>
            </div>
            <nav class="sidebar-nav">
                <a href="donor_dashboard.php" class="nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="donor_profile.php" class="nav-item active">
                    <span>👤</span> My Profile
                </a>
                <a href="donor_appointments.php" class="nav-item">
                    <span>📅</span> Appointments
                </a>
                <a href="donor_history.php" class="nav-item">
                    <span>💉</span> Donation History
                </a>
                <a href="donor_notifications.php" class="nav-item">
                    <span>🔔</span> Notifications
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>My Profile</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($donor['full_name']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Profile updated successfully!</div>
            <?php endif; ?>

            <!-- Profile Information -->
            <section class="dashboard-section">
                <h2>Personal Information</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($donor['full_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($donor['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($donor['email']); ?></p>
                        <p><strong>Blood Type:</strong> <span class="badge" style="font-size: 16px;"><?php echo $donor['blood_type']; ?></span></p>
                    </div>
                    <div>
                        <p><strong>Date of Birth:</strong> <?php echo format_date($donor['date_of_birth']); ?></p>
                        <p><strong>Gender:</strong> <?php echo ucfirst($donor['gender']); ?></p>
                        <p><strong>Joined:</strong> <?php echo format_date($donor['created_at']); ?></p>
                        <p><strong>Total Donations:</strong> <?php echo $donor['total_donations']; ?></p>
                    </div>
                </div>
            </section>

            <!-- Update Contact Information -->
            <section class="dashboard-section">
                <h2>Update Contact Information</h2>
                <form method="POST" action="" style="max-width: 600px;">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($donor['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="street">Street Address</label>
                        <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($donor['street_address']); ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($donor['city']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($donor['pincode']); ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </section>

            <!-- Donation Statistics -->
            <section class="dashboard-section">
                <h2>My Donation Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">💉</div>
                        <div class="stat-details">
                            <h3><?php echo $donor['total_donations']; ?></h3>
                            <p>Total Donations</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">❤️</div>
                        <div class="stat-details">
                            <h3><?php echo $donor['total_donations'] * 3; ?></h3>
                            <p>Lives Potentially Saved</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">📅</div>
                        <div class="stat-details">
                            <h3><?php echo $donor['last_donation_date'] ? format_date($donor['last_donation_date']) : 'Never'; ?></h3>
                            <p>Last Donation</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
