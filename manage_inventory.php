<?php
require_once 'config.php';
check_login('admin');

// Handle inventory update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_inventory'])) {
    $blood_type = sanitize_input($_POST['blood_type']);
    $change_type = $_POST['change_type'];
    $quantity = intval($_POST['quantity']);

    $quantity_change = ($change_type === 'add') ? $quantity : -$quantity;

    if (update_blood_inventory($blood_type, $quantity_change)) {
        $action = ($change_type === 'add') ? "Added" : "Removed";
        log_action(get_user_id(), "INVENTORY_UPDATE", "$action $quantity ml of $blood_type blood");
        header("Location: manage_inventory.php?success=inventory_updated");
        exit();
    }
}

// Get blood inventory
$inventory = get_blood_inventory();

// Get recent inventory changes from logs
$recent_changes = $conn->query("SELECT * FROM system_logs WHERE action = 'INVENTORY_UPDATE' ORDER BY created_at DESC LIMIT 20");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory - BloodLife</title>
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
                <a href="admin_dashboard.php" class="nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="manage_donors.php" class="nav-item">
                    <span>👥</span> Manage Donors
                </a>
                <a href="manage_hospitals.php" class="nav-item">
                    <span>🏥</span> Manage Hospitals
                </a>
                <a href="manage_inventory.php" class="nav-item active">
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
                <h1>Blood Inventory Management</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Inventory updated successfully!</div>
            <?php endif; ?>

            <!-- Current Inventory -->
            <section class="dashboard-section">
                <h2>Current Blood Inventory</h2>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Update Inventory Form -->
            <section class="dashboard-section">
                <h2>Update Inventory</h2>
                <form method="POST" action="" style="max-width: 600px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select id="blood_type" name="blood_type" required>
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
                            <label for="change_type">Action</label>
                            <select id="change_type" name="change_type" required>
                                <option value="add">Add Stock</option>
                                <option value="remove">Remove Stock</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="quantity">Quantity (ml)</label>
                            <input type="number" id="quantity" name="quantity" required min="1" step="50" placeholder="e.g., 450">
                        </div>
                    </div>

                    <button type="submit" name="update_inventory" class="btn btn-primary">Update Inventory</button>
                </form>
            </section>

            <!-- Recent Changes -->
            <section class="dashboard-section">
                <h2>Recent Inventory Changes</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Action</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_changes->num_rows > 0): ?>
                                <?php while ($change = $recent_changes->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo format_datetime($change['created_at']); ?></td>
                                        <td><?php echo htmlspecialchars($change['description']); ?></td>
                                        <td><?php echo htmlspecialchars($change['ip_address']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 30px; color: #999;">No inventory changes yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Inventory Statistics -->
            <section class="dashboard-section">
                <h2>Inventory Statistics</h2>
                <div class="stats-grid">
                    <?php
                    $total_stock = array_sum($inventory);
                    $low_stock_count = count(array_filter($inventory, function($qty) { return $qty < 3000; }));
                    $critical_stock_count = count(array_filter($inventory, function($qty) { return $qty < 1000; }));
                    ?>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: #4CAF50;">🩸</div>
                        <div class="stat-details">
                            <h3><?php echo $total_stock; ?> ml</h3>
                            <p>Total Blood Stock</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #FFC107;">⚠️</div>
                        <div class="stat-details">
                            <h3><?php echo $low_stock_count; ?></h3>
                            <p>Low Stock Types</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #FF5722;">🚨</div>
                        <div class="stat-details">
                            <h3><?php echo $critical_stock_count; ?></h3>
                            <p>Critical Stock Types</p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: #2196F3;">📊</div>
                        <div class="stat-details">
                            <h3>8</h3>
                            <p>Blood Types Tracked</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
