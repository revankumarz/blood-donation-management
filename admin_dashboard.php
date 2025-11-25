<?php
require_once 'config.php';
check_login('admin');

// Get statistics
$total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
$total_hospitals = $conn->query("SELECT COUNT(*) as count FROM hospitals")->fetch_assoc()['count'];
$total_donations = $conn->query("SELECT COUNT(*) as count FROM donations")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'pending'")->fetch_assoc()['count'];

// Get blood inventory
$inventory = get_blood_inventory();

// Get recent donors
$recent_donors = $conn->query("SELECT d.*, u.username, u.email FROM donors d JOIN users u ON d.user_id = u.user_id ORDER BY d.donor_id DESC LIMIT 10");

// Get recent hospitals
$recent_hospitals = $conn->query("SELECT h.*, u.username, u.email FROM hospitals h JOIN users u ON h.user_id = u.user_id ORDER BY h.hospital_id DESC LIMIT 10");

// Get recent donations
$recent_donations = $conn->query("SELECT dn.*, d.full_name as donor_name, h.hospital_name FROM donations dn JOIN donors d ON dn.donor_id = d.donor_id LEFT JOIN hospitals h ON dn.hospital_id = h.hospital_id ORDER BY dn.donation_date DESC LIMIT 10");

// Get pending blood requests
$blood_requests = $conn->query("SELECT br.*, h.hospital_name FROM blood_requests br JOIN hospitals h ON br.hospital_id = h.hospital_id WHERE br.status = 'pending' ORDER BY br.urgency DESC, br.required_date ASC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Admin</span>
            </div>

            <nav class="sidebar-nav">
                <a href="admin_dashboard.php" class="nav-item active">
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
                <a href="reports.php" class="nav-item">
                    <span>📈</span> Reports
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Admin Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_donors; ?></h3>
                        <p>Total Donors</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">🏥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_hospitals; ?></h3>
                        <p>Registered Hospitals</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF5722;">💉</div>
                    <div class="stat-details">
                        <h3><?php echo $total_donations; ?></h3>
                        <p>Total Donations</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFC107;">📋</div>
                    <div class="stat-details">
                        <h3><?php echo $pending_requests; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
            </div>

            <!-- Blood Inventory -->
            <section class="dashboard-section">
                <h2>Blood Inventory Overview</h2>
                <div class="blood-inventory-grid">
                    <?php foreach ($inventory as $blood_type => $quantity): ?>
                        <div class="blood-type-card">
                            <h3><?php echo $blood_type; ?></h3>
                            <p class="quantity"><?php echo $quantity; ?> ml</p>
                            <div class="status-bar">
                                <div class="status-fill" style="width: <?php echo min(($quantity / 5000) * 100, 100); ?>%; background: <?php echo $quantity < 1000 ? '#ff5722' : ($quantity < 3000 ? '#ffc107' : '#4caf50'); ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Pending Blood Requests -->
            <?php if ($blood_requests->num_rows > 0): ?>
            <section class="dashboard-section">
                <h2>Pending Blood Requests</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hospital</th>
                                <th>Blood Type</th>
                                <th>Quantity</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($request = $blood_requests->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                    <td><span class="badge"><?php echo $request['blood_type']; ?></span></td>
                                    <td><?php echo $request['quantity_ml']; ?> ml</td>
                                    <td>
                                        <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>">
                                            <?php echo ucfirst($request['urgency']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($request['required_date']); ?></td>
                                    <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
            <?php endif; ?>

            <!-- Recent Donors -->
            <section class="dashboard-section">
                <h2>Recent Donor Registrations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Blood Type</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($donor = $recent_donors->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                    <td><span class="badge"><?php echo $donor['blood_type']; ?></span></td>
                                    <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['city']); ?></td>
                                    <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Recent Hospitals -->
            <section class="dashboard-section">
                <h2>Recent Hospital Registrations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Hospital Name</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($hospital = $recent_hospitals->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['phone']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['city']); ?></td>
                                    <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
