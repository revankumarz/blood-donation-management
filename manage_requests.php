<?php
require_once 'config.php';
check_login('admin');

// Handle request actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $request_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'fulfill' && isset($_GET['quantity'])) {
        $quantity = intval($_GET['quantity']);
        $request = $conn->query("SELECT * FROM blood_requests WHERE request_id = $request_id")->fetch_assoc();

        // Update inventory (remove blood)
        update_blood_inventory($request['blood_type'], -$quantity);

        // Update request
        $new_fulfilled = $request['fulfilled_quantity_ml'] + $quantity;
        $new_status = ($new_fulfilled >= $request['quantity_ml']) ? 'fulfilled' : 'partial';

        $stmt = $conn->prepare("UPDATE blood_requests SET fulfilled_quantity_ml = ?, status = ? WHERE request_id = ?");
        $stmt->bind_param("isi", $new_fulfilled, $new_status, $request_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_requests.php?success=fulfilled");
        exit();
    } elseif ($action === 'cancel') {
        $conn->query("UPDATE blood_requests SET status = 'cancelled' WHERE request_id = $request_id");
        header("Location: manage_requests.php?success=cancelled");
        exit();
    }
}

// Get blood requests
$requests = $conn->query("SELECT br.*, h.hospital_name, h.city
    FROM blood_requests br
    JOIN hospitals h ON br.hospital_id = h.hospital_id
    ORDER BY
        CASE br.urgency
            WHEN 'critical' THEN 1
            WHEN 'high' THEN 2
            WHEN 'medium' THEN 3
            WHEN 'low' THEN 4
        END,
        br.required_date ASC");

// Get inventory
$inventory = get_blood_inventory();

// Statistics
$total_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests")->fetch_assoc()['count'];
$pending_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'pending'")->fetch_assoc()['count'];
$fulfilled_requests = $conn->query("SELECT COUNT(*) as count FROM blood_requests WHERE status = 'fulfilled'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blood Requests - BloodLife</title>
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
                <a href="manage_requests.php" class="nav-item active">
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

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Manage Blood Requests</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'fulfilled') echo "Request fulfilled successfully!";
                    elseif ($_GET['success'] === 'cancelled') echo "Request cancelled successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">📋</div>
                    <div class="stat-details">
                        <h3><?php echo $total_requests; ?></h3>
                        <p>Total Requests</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $pending_requests; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #667eea;">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $fulfilled_requests; ?></h3>
                        <p>Fulfilled Requests</p>
                    </div>
                </div>
            </div>

            <!-- Blood Requests List -->
            <section class="dashboard-section">
                <h2>All Blood Requests</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hospital</th>
                                <th>City</th>
                                <th>Blood Type</th>
                                <th>Quantity Needed</th>
                                <th>Fulfilled</th>
                                <th>Available in Stock</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php while ($request = $requests->fetch_assoc()): ?>
                                    <?php
                                    $available_stock = $inventory[$request['blood_type']] ?? 0;
                                    $remaining = $request['quantity_ml'] - $request['fulfilled_quantity_ml'];
                                    $can_fulfill = $available_stock >= $remaining;
                                    ?>
                                    <tr>
                                        <td>#<?php echo $request['request_id']; ?></td>
                                        <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['city']); ?></td>
                                        <td><span class="badge"><?php echo $request['blood_type']; ?></span></td>
                                        <td><?php echo $request['quantity_ml']; ?> ml</td>
                                        <td><?php echo $request['fulfilled_quantity_ml']; ?> ml</td>
                                        <td style="color: <?php echo $can_fulfill ? '#28a745' : '#dc3545'; ?>; font-weight: bold;">
                                            <?php echo $available_stock; ?> ml
                                        </td>
                                        <td>
                                            <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>">
                                                <?php echo ucfirst($request['urgency']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($request['required_date']); ?></td>
                                        <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                        <td>
                                            <?php if ($request['status'] === 'pending' || $request['status'] === 'partial'): ?>
                                                <div style="display: flex; gap: 5px; flex-direction: column;">
                                                    <?php if ($can_fulfill): ?>
                                                        <a href="?action=fulfill&id=<?php echo $request['request_id']; ?>&quantity=<?php echo $remaining; ?>"
                                                           onclick="return confirm('Fulfill this request with <?php echo $remaining; ?> ml?')"
                                                           class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Fulfill All</a>
                                                    <?php elseif ($available_stock > 0): ?>
                                                        <a href="?action=fulfill&id=<?php echo $request['request_id']; ?>&quantity=<?php echo $available_stock; ?>"
                                                           onclick="return confirm('Partially fulfill with <?php echo $available_stock; ?> ml?')"
                                                           class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Partial (<?php echo $available_stock; ?>ml)</a>
                                                    <?php else: ?>
                                                        <span style="font-size: 11px; color: #dc3545;">No stock</span>
                                                    <?php endif; ?>
                                                    <a href="?action=cancel&id=<?php echo $request['request_id']; ?>"
                                                       onclick="return confirm('Cancel this request?')"
                                                       class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Cancel</a>
                                                </div>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 30px;">No blood requests found</td>
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
