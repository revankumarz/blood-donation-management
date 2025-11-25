<?php
require_once 'config.php';
check_login('donor');

$user_id = get_user_id();

// Get donor info
$donor = $conn->query("SELECT * FROM donors WHERE user_id = $user_id")->fetch_assoc();

// Handle cancel appointment
if (isset($_GET['cancel']) && isset($_GET['id'])) {
    $appointment_id = intval($_GET['id']);
    $conn->query("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = $appointment_id AND donor_id = " . $donor['donor_id']);
    header("Location: donor_appointments.php?success=cancelled");
    exit();
}

// Get all appointments
$appointments = $conn->query("SELECT a.*, h.hospital_name
    FROM appointments a
    LEFT JOIN hospitals h ON a.hospital_id = h.hospital_id
    WHERE a.donor_id = " . $donor['donor_id'] . "
    ORDER BY a.appointment_date DESC, a.appointment_time DESC");

// Get upcoming and past
$upcoming = $conn->query("SELECT a.*, h.hospital_name
    FROM appointments a
    LEFT JOIN hospitals h ON a.hospital_id = h.hospital_id
    WHERE a.donor_id = " . $donor['donor_id'] . " AND a.appointment_date >= CURDATE() AND a.status = 'scheduled'
    ORDER BY a.appointment_date ASC, a.appointment_time ASC");

$past = $conn->query("SELECT a.*, h.hospital_name
    FROM appointments a
    LEFT JOIN hospitals h ON a.hospital_id = h.hospital_id
    WHERE a.donor_id = " . $donor['donor_id'] . " AND (a.appointment_date < CURDATE() OR a.status != 'scheduled')
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - BloodLife</title>
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
                <a href="donor_profile.php" class="nav-item">
                    <span>👤</span> My Profile
                </a>
                <a href="donor_appointments.php" class="nav-item active">
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
                <h1>My Appointments</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($donor['full_name']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Appointment cancelled successfully!</div>
            <?php endif; ?>

            <!-- Upcoming Appointments -->
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($upcoming->num_rows > 0): ?>
                                <?php while ($appointment = $upcoming->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo format_date($appointment['appointment_date']); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo $appointment['hospital_name'] ?? 'Not Assigned'; ?></td>
                                        <td><span class="status-badge status-<?php echo $appointment['status']; ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                        <td>
                                            <a href="?cancel=1&id=<?php echo $appointment['appointment_id']; ?>" onclick="return confirm('Cancel this appointment?')" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Cancel</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;">
                                        No upcoming appointments. <a href="donor_dashboard.php">Schedule one now</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Past Appointments -->
            <section class="dashboard-section">
                <h2>Past Appointments</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Hospital</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($past->num_rows > 0): ?>
                                <?php while ($appointment = $past->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo format_date($appointment['appointment_date']); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></td>
                                        <td><?php echo $appointment['hospital_name'] ?? 'Not Assigned'; ?></td>
                                        <td><span class="status-badge status-<?php echo $appointment['status']; ?>"><?php echo ucfirst($appointment['status']); ?></span></td>
                                        <td><?php echo $appointment['notes'] ?: '-'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;">No past appointments</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
