<?php
require_once 'config.php';
check_login('donor');

$user_id = get_user_id();

// Get donor info
$donor = $conn->query("SELECT * FROM donors WHERE user_id = $user_id")->fetch_assoc();

// Get donation history
$donations = $conn->query("SELECT d.*, h.hospital_name
    FROM donations d
    LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
    WHERE d.donor_id = " . $donor['donor_id'] . "
    ORDER BY d.donation_date DESC");

// Statistics
$stats = [
    'total' => $donor['total_donations'],
    'total_ml' => $conn->query("SELECT SUM(quantity_ml) as total FROM donations WHERE donor_id = " . $donor['donor_id'] . " AND status = 'completed'")->fetch_assoc()['total'] ?? 0,
    'last_year' => $conn->query("SELECT COUNT(*) as count FROM donations WHERE donor_id = " . $donor['donor_id'] . " AND donation_date >= DATE_SUB(NOW(), INTERVAL 1 YEAR)")->fetch_assoc()['count'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation History - BloodLife</title>
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
                <a href="donor_appointments.php" class="nav-item">
                    <span>📅</span> Appointments
                </a>
                <a href="donor_history.php" class="nav-item active">
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
                <h1>My Donation History</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($donor['full_name']); ?></span>
                </div>
            </header>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">💉</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total']; ?></h3>
                        <p>Total Donations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">🩸</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_ml']); ?> ml</h3>
                        <p>Total Blood Donated</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">📅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['last_year']; ?></h3>
                        <p>Donations in Last Year</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">❤️</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total'] * 3; ?></h3>
                        <p>Lives Potentially Saved</p>
                    </div>
                </div>
            </div>

            <!-- Donation History -->
            <section class="dashboard-section">
                <h2>All Donations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Blood Type</th>
                                <th>Quantity</th>
                                <th>Hospital</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($donations->num_rows > 0): ?>
                                <?php while ($donation = $donations->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo format_date($donation['donation_date']); ?></td>
                                        <td><span class="badge"><?php echo $donation['blood_type']; ?></span></td>
                                        <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                        <td><?php echo $donation['hospital_name'] ?? 'N/A'; ?></td>
                                        <td><span class="status-badge status-<?php echo $donation['status']; ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                        <td><?php echo $donation['notes'] ?: '-'; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">
                                        No donations yet. <a href="donor_dashboard.php">Schedule your first donation!</a>
                                    </td>
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
