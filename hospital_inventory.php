<?php
require_once 'config.php';
check_login('hospital');

$user_id = get_user_id();
$hospital = $conn->query("SELECT * FROM hospitals WHERE user_id = $user_id")->fetch_assoc();

// Get blood inventory
$inventory = get_blood_inventory();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - BloodLife</title>
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
                <a href="hospital_inventory.php" class="nav-item active">
                    <span>🩸</span> Blood Inventory
                </a>
                <a href="hospital_donors.php" class="nav-item">
                    <span>👥</span> Find Donors
                </a>
                <a href="hospital_profile.php" class="nav-item">
                    <span>🏥</span> Hospital Profile
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Current Blood Inventory</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                </div>
            </header>

            <section class="dashboard-section">
                <h2>Available Blood Stock</h2>
                <div class="blood-inventory-grid">
                    <?php foreach ($inventory as $blood_type => $quantity): ?>
                        <div class="blood-type-card">
                            <h3><?php echo $blood_type; ?></h3>
                            <p class="quantity"><?php echo $quantity; ?> ml</p>
                            <div class="status-bar">
                                <div class="status-fill" style="width: <?php echo min(($quantity / 5000) * 100, 100); ?>%; background: <?php echo $quantity < 1000 ? '#ff5722' : ($quantity < 3000 ? '#ffc107' : '#4caf50'); ?>;"></div>
                            </div>
                            <p style="font-size: 12px; margin-top: 8px; font-weight: 600; color: <?php echo $quantity < 1000 ? '#ff5722' : ($quantity < 3000 ? '#ffc107' : '#4caf50'); ?>;">
                                <?php echo $quantity < 1000 ? 'Critical Low' : ($quantity < 3000 ? 'Low Stock' : 'Available'); ?>
                            </p>
                            <?php if ($quantity < 3000): ?>
                                <a href="hospital_dashboard.php" style="display: block; margin-top: 10px; padding: 8px; background: #667eea; color: white; text-align: center; border-radius: 6px; text-decoration: none; font-size: 12px;">Request Blood</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dashboard-section">
                <h2>Inventory Details</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Blood Type</th>
                                <th>Quantity Available</th>
                                <th>Stock Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $blood_type => $quantity): ?>
                                <tr>
                                    <td><span class="badge" style="font-size: 16px;"><?php echo $blood_type; ?></span></td>
                                    <td style="font-size: 18px; font-weight: bold;"><?php echo number_format($quantity); ?> ml</td>
                                    <td>
                                        <span class="status-badge <?php echo $quantity < 1000 ? 'status-cancelled' : ($quantity < 3000 ? 'status-pending' : 'status-completed'); ?>">
                                            <?php echo $quantity < 1000 ? 'Critical' : ($quantity < 3000 ? 'Low' : 'Good'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($quantity < 3000): ?>
                                            <a href="hospital_dashboard.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 13px;">Request More</a>
                                        <?php else: ?>
                                            <span style="color: #28a745; font-weight: bold;">✓ Sufficient</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
