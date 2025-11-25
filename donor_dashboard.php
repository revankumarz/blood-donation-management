<?php
require_once 'config.php';
check_login('donor');

$user_id = get_user_id();

// Get donor information
$stmt = $conn->prepare("SELECT d.*, u.username, u.email FROM donors d JOIN users u ON d.user_id = u.user_id WHERE d.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get donation statistics
$donation_stats = $conn->query("SELECT COUNT(*) as total_donations, SUM(quantity_ml) as total_ml FROM donations WHERE donor_id = " . $donor['donor_id'])->fetch_assoc();

// Get donation history
$donation_history = $conn->query("SELECT dn.*, h.hospital_name FROM donations dn LEFT JOIN hospitals h ON dn.hospital_id = h.hospital_id WHERE dn.donor_id = " . $donor['donor_id'] . " ORDER BY dn.donation_date DESC LIMIT 10");

// Get upcoming appointments
$upcoming_appointments = $conn->query("SELECT a.*, h.hospital_name FROM appointments a LEFT JOIN hospitals h ON a.hospital_id = h.hospital_id WHERE a.donor_id = " . $donor['donor_id'] . " AND a.appointment_date >= CURDATE() AND a.status = 'scheduled' ORDER BY a.appointment_date ASC");

// Get notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");

// Check eligibility
$is_eligible = calculate_donor_eligibility($donor['last_donation_date']);

// Handle appointment scheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['schedule_appointment'])) {
    $appointment_date = sanitize_input($_POST['appointment_date']);
    $appointment_time = sanitize_input($_POST['appointment_time']);

    if ($is_eligible) {
        $stmt = $conn->prepare("INSERT INTO appointments (donor_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, 'scheduled')");
        $stmt->bind_param("iss", $donor['donor_id'], $appointment_date, $appointment_time);

        if ($stmt->execute()) {
            create_notification($user_id, "Appointment Scheduled", "Your blood donation appointment has been scheduled for " . format_date($appointment_date), "success");
            log_action($user_id, "APPOINTMENT_SCHEDULED", "Appointment scheduled for $appointment_date");
            header("Location: donor_dashboard.php?success=appointment_scheduled");
            exit();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Donor</span>
            </div>

            <nav class="sidebar-nav">
                <a href="donor_dashboard.php" class="nav-item active">
                    <span>📊</span> Dashboard
                </a>
                <a href="donor_profile.php" class="nav-item">
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

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Donor Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($donor['full_name']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'appointment_scheduled') {
                        echo "Appointment scheduled successfully!";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Donor Info Card -->
            <div class="dashboard-section">
                <h2>Your Information</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <p><strong>Blood Type:</strong> <span class="badge" style="font-size: 16px;"><?php echo $donor['blood_type']; ?></span></p>
                    </div>
                    <div>
                        <p><strong>Total Donations:</strong> <?php echo $donor['total_donations']; ?></p>
                    </div>
                    <div>
                        <p><strong>Last Donation:</strong> <?php echo $donor['last_donation_date'] ? format_date($donor['last_donation_date']) : 'Never'; ?></p>
                    </div>
                    <div>
                        <p><strong>Eligibility Status:</strong>
                            <span class="status-badge <?php echo $is_eligible ? 'status-completed' : 'status-pending'; ?>">
                                <?php echo $is_eligible ? 'Eligible to Donate' : 'Not Eligible Yet'; ?>
                            </span>
                        </p>
                    </div>
                </div>
                <?php if (!$is_eligible && $donor['last_donation_date']): ?>
                    <div style="margin-top: 15px; padding: 12px; background: #fff3cd; border-radius: 8px; color: #856404;">
                        You must wait at least 56 days (8 weeks) between donations. You can donate again after <?php echo date('M d, Y', strtotime($donor['last_donation_date'] . ' + 56 days')); ?>.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">💉</div>
                    <div class="stat-details">
                        <h3><?php echo $donation_stats['total_donations'] ?? 0; ?></h3>
                        <p>Total Donations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF5722;">🩸</div>
                    <div class="stat-details">
                        <h3><?php echo $donation_stats['total_ml'] ?? 0; ?> ml</h3>
                        <p>Total Blood Donated</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">❤️</div>
                    <div class="stat-details">
                        <h3><?php echo ($donation_stats['total_donations'] ?? 0) * 3; ?></h3>
                        <p>Lives Potentially Saved</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFC107;">📅</div>
                    <div class="stat-details">
                        <h3><?php echo $upcoming_appointments->num_rows; ?></h3>
                        <p>Upcoming Appointments</p>
                    </div>
                </div>
            </div>

            <!-- Schedule Appointment -->
            <?php if ($is_eligible): ?>
            <section class="dashboard-section">
                <h2>Schedule Blood Donation Appointment</h2>
                <form method="POST" action="" style="max-width: 600px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="appointment_date">Appointment Date</label>
                            <input type="date" id="appointment_date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="appointment_time">Appointment Time</label>
                            <input type="time" id="appointment_time" name="appointment_time" required>
                        </div>
                    </div>
                    <button type="submit" name="schedule_appointment" class="btn btn-primary">Schedule Appointment</button>
                </form>
            </section>
            <?php endif; ?>

            <!-- Upcoming Appointments -->
            <?php if ($upcoming_appointments->num_rows > 0): ?>
            <section class="dashboard-section">
                <h2>Upcoming Appointments</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Hospital</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($appointment = $upcoming_appointments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo format_date($appointment['appointment_date']); ?></td>
                                    <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                    <td><?php echo $appointment['hospital_name'] ?? 'Not Assigned'; ?></td>
                                    <td><span class="status-badge status-<?php echo $appointment['status']; ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Donation History -->
            <?php if ($donation_history->num_rows > 0): ?>
            <section class="dashboard-section">
                <h2>Recent Donation History</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Quantity</th>
                                <th>Hospital</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($donation = $donation_history->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo format_date($donation['donation_date']); ?></td>
                                    <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                    <td><?php echo $donation['hospital_name'] ?? 'Unknown'; ?></td>
                                    <td><span class="status-badge status-<?php echo $donation['status']; ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Notifications -->
            <?php if ($notifications->num_rows > 0): ?>
            <section class="dashboard-section">
                <h2>Recent Notifications</h2>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <?php while ($notification = $notifications->fetch_assoc()): ?>
                        <div style="padding: 15px; background: #f8f9fa; border-left: 4px solid <?php echo get_urgency_color($notification['type']); ?>; border-radius: 8px;">
                            <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($notification['title']); ?></h4>
                            <p style="font-size: 14px; color: #666; margin-bottom: 5px;"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <small style="color: #999;"><?php echo format_datetime($notification['created_at']); ?></small>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
