<?php
require_once 'config.php';
check_login('hospital');

$user_id = get_user_id();

// Get hospital information
$stmt = $conn->prepare("SELECT h.*, u.username, u.email, u.created_at FROM hospitals h JOIN users u ON h.user_id = u.user_id WHERE h.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hospital = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone = sanitize_input($_POST['phone']);
    $street = sanitize_input($_POST['street']);
    $city = sanitize_input($_POST['city']);
    $pincode = sanitize_input($_POST['pincode']);

    $stmt = $conn->prepare("UPDATE hospitals SET phone = ?, street_address = ?, city = ?, pincode = ? WHERE user_id = ?");
    $stmt->bind_param("ssssi", $phone, $street, $city, $pincode, $user_id);

    if ($stmt->execute()) {
        header("Location: hospital_profile.php?success=updated");
        exit();
    }
    $stmt->close();
}

// Get statistics
$stats = [
    'total_requests' => $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'])->fetch_assoc()['count'],
    'pending' => $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " AND status = 'pending'")->fetch_assoc()['count'],
    'fulfilled' => $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " AND status = 'fulfilled'")->fetch_assoc()['count'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Profile - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Hospital</span>
            </div>
            <nav class="sidebar-nav">
                <a href="hospital_dashboard.php" class="nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="hospital_requests.php" class="nav-item">
                    <span>📋</span> Blood Requests
                </a>
                <a href="hospital_inventory.php" class="nav-item">
                    <span>🩸</span> Blood Inventory
                </a>
                <a href="hospital_donors.php" class="nav-item">
                    <span>👥</span> Find Donors
                </a>
                <a href="hospital_profile.php" class="nav-item active">
                    <span>🏥</span> Hospital Profile
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Hospital Profile</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Profile updated successfully!</div>
            <?php endif; ?>

            <!-- Hospital Information -->
            <section class="dashboard-section">
                <h2>Hospital Information</h2>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <div>
                        <p><strong>Hospital Name:</strong> <?php echo htmlspecialchars($hospital['hospital_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($hospital['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($hospital['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($hospital['phone']); ?></p>
                    </div>
                    <div>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($hospital['street_address']); ?></p>
                        <p><strong>City:</strong> <?php echo htmlspecialchars($hospital['city']); ?></p>
                        <p><strong>Pincode:</strong> <?php echo htmlspecialchars($hospital['pincode']); ?></p>
                        <p><strong>Joined:</strong> <?php echo format_date($hospital['created_at']); ?></p>
                    </div>
                </div>
            </section>

            <!-- Update Contact Information -->
            <section class="dashboard-section">
                <h2>Update Contact Information</h2>
                <form method="POST" action="" style="max-width: 600px;">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($hospital['phone']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="street">Street Address</label>
                        <input type="text" id="street" name="street" value="<?php echo htmlspecialchars($hospital['street_address']); ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($hospital['city']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" value="<?php echo htmlspecialchars($hospital['pincode']); ?>">
                        </div>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </section>

            <!-- Request Statistics -->
            <section class="dashboard-section">
                <h2>Request Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">📋</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_requests']; ?></h3>
                            <p>Total Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">⏳</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['pending']; ?></h3>
                            <p>Pending Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">✅</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['fulfilled']; ?></h3>
                            <p>Fulfilled Requests</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
