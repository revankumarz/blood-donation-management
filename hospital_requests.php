<?php
require_once 'config.php';
check_login('hospital');

$user_id = get_user_id();
$hospital = $conn->query("SELECT * FROM hospitals WHERE user_id = $user_id")->fetch_assoc();

// Get all requests for this hospital
$requests = $conn->query("SELECT * FROM blood_requests WHERE hospital_id = " . $hospital['hospital_id'] . " ORDER BY request_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Blood Requests - BloodLife</title>
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
                <a href="hospital_requests.php" class="nav-item active">
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

        <main class="main-content">
            <header class="dashboard-header">
                <h1>My Blood Requests</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                </div>
            </header>

            <section class="dashboard-section">
                <h2>All Requests</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Blood Type</th>
                                <th>Quantity Requested</th>
                                <th>Quantity Fulfilled</th>
                                <th>Remaining</th>
                                <th>Urgency</th>
                                <th>Required Date</th>
                                <th>Request Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($requests->num_rows > 0): ?>
                                <?php while ($request = $requests->fetch_assoc()): ?>
                                    <?php $remaining = $request['quantity_ml'] - $request['fulfilled_quantity_ml']; ?>
                                    <tr>
                                        <td>#<?php echo $request['request_id']; ?></td>
                                        <td><span class="badge"><?php echo $request['blood_type']; ?></span></td>
                                        <td><?php echo $request['quantity_ml']; ?> ml</td>
                                        <td><?php echo $request['fulfilled_quantity_ml']; ?> ml</td>
                                        <td style="font-weight: bold; color: <?php echo $remaining > 0 ? '#dc3545' : '#28a745'; ?>;">
                                            <?php echo $remaining; ?> ml
                                        </td>
                                        <td>
                                            <span class="urgency-badge urgency-<?php echo $request['urgency']; ?>">
                                                <?php echo ucfirst($request['urgency']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo format_date($request['required_date']); ?></td>
                                        <td><?php echo format_datetime($request['request_date']); ?></td>
                                        <td><span class="status-badge status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 30px;">
                                        No requests yet. <a href="hospital_dashboard.php">Create a new request</a>
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
