<?php
require_once 'config.php';
check_login('hospital');

$user_id = get_user_id();

// Get hospital information
$stmt = $conn->prepare("SELECT h.*, u.username, u.email FROM hospitals h JOIN users u ON h.user_id = u.user_id WHERE h.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$hospital = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get blood inventory
$inventory = get_blood_inventory();

// Get hospital statistics
$total_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'])->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " AND status = 'pending'")->fetch_assoc()['count'];
$fulfilled_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " AND status = 'fulfilled'")->fetch_assoc()['count'];

// Get recent blood requests
$blood_requests = $conn->query("SELECT * FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " ORDER BY request_date DESC LIMIT 10");

// Handle new blood request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    $blood_type = sanitize_input($_POST['blood_type']);
    $quantity_ml = intval($_POST['quantity_ml']);
    $urgency = sanitize_input($_POST['urgency']);
    $required_date = sanitize_input($_POST['required_date']);
    $notes = sanitize_input($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO blood_requests (hospital_id, blood_type, quantity_ml, urgency, required_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $hospital['hospital_id'], $blood_type, $quantity_ml, $urgency, $required_date, $notes);

    if ($stmt->execute()) {
        log_action($user_id, "BLOOD_REQUEST_CREATED", "Blood request created: $blood_type ($quantity_ml ml)");
        header("Location: hospital_dashboard.php?success=request_submitted");
        exit();
    }
    $stmt->close();
}

// Handle request cancellation
if (isset($_GET['cancel_request'])) {
    $request_id = intval($_GET['cancel_request']);
    $stmt = $conn->prepare("UPDATE blood_requests SET status = 'cancelled' WHERE request_id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $request_id, $hospital['hospital_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: hospital_dashboard.php?success=request_cancelled");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Hospital</span>
            </div>

            <nav class="sidebar-nav">
                <a href="hospital_dashboard.php" class="nav-item active">
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
                <a href="hospital_profile.php" class="nav-item">
                    <span>🏥</span> Hospital Profile
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Hospital Dashboard</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'request_submitted') {
                        echo "Blood request submitted successfully!";
                    } elseif ($_GET['success'] === 'request_cancelled') {
                        echo "Blood request cancelled successfully!";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">📋</div>
                    <div class="stat-details">
                        <h3><?php echo $total_requests; ?></h3>
                        <p>Total Requests</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FFC107;">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $pending_requests; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $fulfilled_requests; ?></h3>
                        <p>Fulfilled Requests</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF5722;">🩸</div>
                    <div class="stat-details">
                        <h3>8</h3>
                        <p>Blood Types Available</p>
                    </div>
                </div>
            </div>

            <!-- Blood Inventory -->
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
                            <p style="font-size: 12px; margin-top: 5px; color: <?php echo $quantity < 1000 ? '#ff5722' : ($quantity < 3000 ? '#ffc107' : '#4caf50'); ?>;">
                                <?php echo $quantity < 1000 ? 'Critical Low' : ($quantity < 3000 ? 'Low Stock' : 'Available'); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Request Blood Form -->
            <section class="dashboard-section">
                <h2>Request Blood</h2>
                <form method="POST" action="" style="max-width: 800px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
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
                            <label for="quantity_ml">Quantity (ml)</label>
                            <input type="number" id="quantity_ml" name="quantity_ml" required min="100" step="50" placeholder="e.g., 450">
                        </div>

                        <div class="form-group">
                            <label for="urgency">Urgency Level</label>
                            <select id="urgency" name="urgency" required>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="required_date">Required By Date</label>
                            <input type="date" id="required_date" name="required_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any additional information..."></textarea>
                    </div>

                    <button type="submit" name="submit_request" class="btn btn-primary">Submit Request</button>
                </form>
            </section>

            <!-- Recent Blood Requests -->
            <section class="dashboard-section">
                <h2>Recent Blood Requests</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Blood Type</th>
                                <th>Quantity</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Status</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($blood_requests->num_rows > 0): ?>
                                <?php while ($request = $blood_requests->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $request['request_id']; ?></td>
                                        <td><span class="badge"><?php echo $request['blood_type']; ?></span></td>
                                        <td><?php echo $request['quantity_ml']; ?> ml</td>
                                        <td>
                                            <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>">
                                                <?php echo ucfirst($request['urgency']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($request['required_date']); ?></td>
                                        <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                        <td><?php echo format_datetime($request['request_date']); ?></td>
                                        <td>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <a href="?cancel_request=<?php echo $request['request_id']; ?>" onclick="return confirm('Are you sure you want to cancel this request?')" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Cancel</a>
                                            <?php else: ?>
                                                <span style="color: #999;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 30px; color: #999;">No blood requests yet. Submit a request above to get started.</td>
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
