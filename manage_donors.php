<?php
require_once 'config.php';
check_login('admin');

// Handle donor actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $donor_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE users u JOIN donors d ON u.user_id = d.user_id SET u.is_active = 0 WHERE d.donor_id = ?");
        $stmt->bind_param("i", $donor_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_donors.php?success=deactivated");
        exit();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE users u JOIN donors d ON u.user_id = d.user_id SET u.is_active = 1 WHERE d.donor_id = ?");
        $stmt->bind_param("i", $donor_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_donors.php?success=activated");
        exit();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = (SELECT user_id FROM donors WHERE donor_id = ?)");
        $stmt->bind_param("i", $donor_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_donors.php?success=deleted");
        exit();
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$blood_type_filter = $_GET['blood_type'] ?? '';
$city_filter = $_GET['city'] ?? '';

// Build query
$query = "SELECT d.*, u.username, u.email, u.is_active FROM donors d JOIN users u ON d.user_id = u.user_id WHERE 1=1";

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (d.full_name LIKE '%$search_term%' OR u.username LIKE '%$search_term%' OR u.email LIKE '%$search_term%')";
}

if ($blood_type_filter) {
    $query .= " AND d.blood_type = '$blood_type_filter'";
}

if ($city_filter) {
    $city_filter_escaped = $conn->real_escape_string($city_filter);
    $query .= " AND d.city LIKE '%$city_filter_escaped%'";
}

$query .= " ORDER BY d.donor_id DESC";
$donors = $conn->query($query);

// Get statistics
$total_donors = $conn->query("SELECT COUNT(*) as count FROM donors")->fetch_assoc()['count'];
$eligible_donors = $conn->query("SELECT COUNT(*) as count FROM donors WHERE is_eligible = 1")->fetch_assoc()['count'];

// Get blood type distribution
$blood_distribution = $conn->query("SELECT blood_type, COUNT(*) as count FROM donors GROUP BY blood_type");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donors - BloodLife</title>
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
                <a href="manage_donors.php" class="nav-item active">
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

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Manage Donors</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'deactivated') echo "Donor deactivated successfully!";
                    elseif ($_GET['success'] === 'activated') echo "Donor activated successfully!";
                    elseif ($_GET['success'] === 'deleted') echo "Donor deleted successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_donors; ?></h3>
                        <p>Total Donors</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $eligible_donors; ?></h3>
                        <p>Eligible Donors</p>
                    </div>
                </div>
            </div>

            <!-- Blood Type Distribution -->
            <section class="dashboard-section">
                <h2>Donor Blood Type Distribution</h2>
                <div class="blood-inventory-grid">
                    <?php
                    $blood_counts = [];
                    while ($row = $blood_distribution->fetch_assoc()) {
                        $blood_counts[$row['blood_type']] = $row['count'];
                    }

                    $all_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                    foreach ($all_types as $type):
                        $count = $blood_counts[$type] ?? 0;
                    ?>
                        <div class="blood-type-card">
                            <h3><?php echo $type; ?></h3>
                            <p class="quantity"><?php echo $count; ?> donors</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Search and Filter -->
            <section class="dashboard-section">
                <h2>Search & Filter Donors</h2>
                <form method="GET" action="" style="display: grid; grid-template-columns: 2fr 1fr 1fr 100px; gap: 15px;">
                    <input type="text" name="search" placeholder="Search by name, username, or email" value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">

                    <select name="blood_type" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <option value="">All Blood Types</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $blood_type_filter === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="city" placeholder="City" value="<?php echo htmlspecialchars($city_filter); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">

                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </section>

            <!-- Donors List -->
            <section class="dashboard-section">
                <h2>All Donors (<?php echo $donors->num_rows; ?> results)</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Blood Type</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Donations</th>
                                <th>Eligible</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($donors->num_rows > 0): ?>
                                <?php while ($donor = $donors->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $donor['donor_id']; ?></td>
                                        <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['username']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                        <td><span class="badge"><?php echo $donor['blood_type']; ?></span></td>
                                        <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['city']); ?></td>
                                        <td><?php echo $donor['total_donations']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $donor['is_eligible'] ? 'status-completed' : 'status-pending'; ?>">
                                                <?php echo $donor['is_eligible'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?php echo $donor['is_active'] ? 'status-completed' : 'status-cancelled'; ?>">
                                                <?php echo $donor['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <?php if ($donor['is_active']): ?>
                                                    <a href="?action=deactivate&id=<?php echo $donor['donor_id']; ?>" onclick="return confirm('Deactivate this donor?')" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Deactivate</a>
                                                <?php else: ?>
                                                    <a href="?action=activate&id=<?php echo $donor['donor_id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Activate</a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $donor['donor_id']; ?>" onclick="return confirm('Delete this donor permanently?')" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="11" style="text-align: center; padding: 30px;">No donors found</td>
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
