<?php
require_once 'config.php';
check_login('admin');

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Overall Statistics
$stats = [
    'total_donors' => $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'],
    'total_hospitals' => $conn->query("SELECT COUNT(*) as count FROM hospitals")->fetch_assoc()['count'],
    'total_donations' => $conn->query("SELECT COUNT(*) as count FROM donations WHERE donation_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'],
    'total_blood_collected' => $conn->query("SELECT SUM(quantity_ml) as total FROM donations WHERE status = 'completed' AND donation_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['total'] ?? 0,
    'total_requests' => $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE request_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'],
    'fulfilled_requests' => $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'fulfilled' AND request_date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc()['count'],
];

// Blood type statistics
$blood_stats = $conn->query("SELECT blood_type, COUNT(*) as donation_count, SUM(quantity_ml) as total_ml
    FROM donations
    WHERE status = 'completed' AND donation_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY blood_type");

// Top donors
$top_donors = $conn->query("SELECT d.full_name, d.blood_type, COUNT(don.donation_id) as donation_count, SUM(don.quantity_ml) as total_donated
    FROM donors d
    JOIN donations don ON d.donor_id = don.donor_id
    WHERE don.status = 'completed' AND don.donation_date BETWEEN '$start_date' AND '$end_date'
    GROUP BY d.donor_id
    ORDER BY donation_count DESC
    LIMIT 10");

// Monthly donation trend
$monthly_trend = $conn->query("SELECT
    DATE_FORMAT(donation_date, '%Y-%m') as month,
    COUNT(*) as count,
    SUM(quantity_ml) as total_ml
    FROM donations
    WHERE status = 'completed' AND donation_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY month
    ORDER BY month");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Admin</span>
            </div>
            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="manage_donors.php" class="nav-item">
                    <span>👥</span> Manage Donors
                </a>
                <a href="manage_hospitals.php" class="nav-item">
                    <span>🏥</span> Manage Hospitals
                </a>
                <a href="manage_inventory.php" class="nav-item">
                    <span>🩸</span> Blood Inventory
                </a>
                <a href="manage_requests.php" class="nav-item">
                    <span>📋</span> Blood Requests
                </a>
                <a href="manage_donations.php" class="nav-item">
                    <span>💉</span> Donations
                </a>
                <a href="reports.php" class="nav-item active">
                    <span>📈</span> Reports
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Reports & Analytics</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Date Range Filter -->
            <section class="dashboard-section">
                <h2>Filter by Date Range</h2>
                <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr 100px; gap: 15px; max-width: 600px;">
                    <div class="form-group" style="margin: 0;">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?php echo $start_date; ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    <div class="form-group" style="margin: 0;">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?php echo $end_date; ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    </div>
                    <div style="align-self: end;">
                        <button type="submit" class="btn btn-primary">Apply</button>
                    </div>
                </form>
            </section>

            <!-- Overall Statistics -->
            <section class="dashboard-section">
                <h2>Overall Statistics (<?php echo format_date($start_date); ?> to <?php echo format_date($end_date); ?>)</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">👥</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_donors']; ?></h3>
                            <p>Total Donors</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">🏥</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_hospitals']; ?></h3>
                            <p>Total Hospitals</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">💉</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_donations']; ?></h3>
                            <p>Donations in Period</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">🩸</div>
                        <div class="stat-details">
                            <h3><?php echo number_format($stats['total_blood_collected']); ?> ml</h3>
                            <p>Blood Collected</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">📋</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['total_requests']; ?></h3>
                            <p>Total Requests</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #667eea;">✅</div>
                        <div class="stat-details">
                            <h3><?php echo $stats['fulfilled_requests']; ?></h3>
                            <p>Fulfilled Requests</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Blood Type Statistics -->
            <section class="dashboard-section">
                <h2>Donation Statistics by Blood Type</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Blood Type</th>
                                <th>Number of Donations</th>
                                <th>Total Volume Collected</th>
                                <th>Average per Donation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($blood_stats->num_rows > 0): ?>
                                <?php while ($stat = $blood_stats->fetch_assoc()): ?>
                                    <tr>
                                        <td><span class="badge"><?php echo $stat['blood_type']; ?></span></td>
                                        <td><?php echo $stat['donation_count']; ?></td>
                                        <td><?php echo number_format($stat['total_ml']); ?> ml</td>
                                        <td><?php echo round($stat['total_ml'] / $stat['donation_count']); ?> ml</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 30px;">No donation data for this period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Top Donors -->
            <section class="dashboard-section">
                <h2>Top 10 Donors in Period</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Donor Name</th>
                                <th>Blood Type</th>
                                <th>Number of Donations</th>
                                <th>Total Donated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($top_donors->num_rows > 0): ?>
                                <?php
                                $rank = 1;
                                while ($donor = $top_donors->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><?php echo $rank++; ?></td>
                                        <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                        <td><span class="badge"><?php echo $donor['blood_type']; ?></span></td>
                                        <td><?php echo $donor['donation_count']; ?></td>
                                        <td><?php echo number_format($donor['total_donated']); ?> ml</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px;">No donor data for this period</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Monthly Trend -->
            <section class="dashboard-section">
                <h2>6-Month Donation Trend</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Number of Donations</th>
                                <th>Total Blood Collected</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($monthly_trend->num_rows > 0): ?>
                                <?php while ($trend = $monthly_trend->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('F Y', strtotime($trend['month'] . '-01')); ?></td>
                                        <td><?php echo $trend['count']; ?></td>
                                        <td><?php echo number_format($trend['total_ml']); ?> ml</td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 30px;">No trend data available</td>
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
