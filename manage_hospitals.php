<?php
require_once 'config.php';
check_login('admin');

// Handle hospital actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $hospital_id = intval($_GET['id']);
    $action = $_GET['action'];

    if ($action === 'deactivate') {
        $stmt = $conn->prepare("UPDATE users u JOIN hospitals h ON u.user_id = h.user_id SET u.is_active = 0 WHERE h.hospital_id = ?");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_hospitals.php?success=deactivated");
        exit();
    } elseif ($action === 'activate') {
        $stmt = $conn->prepare("UPDATE users u JOIN hospitals h ON u.user_id = h.user_id SET u.is_active = 1 WHERE h.hospital_id = ?");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_hospitals.php?success=activated");
        exit();
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = (SELECT user_id FROM hospitals WHERE hospital_id = ?)");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_hospitals.php?success=deleted");
        exit();
    }
}

// Get search parameters
$search = $_GET['search'] ?? '';
$city_filter = $_GET['city'] ?? '';

// Build query
$query = "SELECT h.*, u.username, u.email, u.is_active,
          (SELECT COUNT(*) FROM blood_requests WHERE hospital_id = h.hospital_id) as total_requests,
          (SELECT COUNT(*) FROM blood_requests WHERE hospital_id = h.hospital_id AND status = 'pending') as pending_requests
          FROM hospitals h JOIN users u ON h.user_id = u.user_id WHERE 1=1";

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $query .= " AND (h.hospital_name LIKE '%$search_term%' OR u.username LIKE '%$search_term%' OR u.email LIKE '%$search_term%')";
}

if ($city_filter) {
    $city_filter_escaped = $conn->real_escape_string($city_filter);
    $query .= " AND h.city LIKE '%$city_filter_escaped%'";
}

$query .= " ORDER BY h.hospital_id DESC";
$hospitals = $conn->query($query);

// Get statistics
$total_hospitals = $conn->query("SELECT COUNT(*) as count FROM hospitals")->fetch_assoc()['count'];
$active_hospitals = $conn->query("SELECT COUNT(*) as count FROM hospitals h JOIN users u ON h.user_id = u.user_id WHERE u.is_active = 1")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Hospitals - BloodLife</title>
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
                <a href="manage_hospitals.php" class="nav-item active">
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
                <h1>Manage Hospitals</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'deactivated') echo "Hospital deactivated successfully!";
                    elseif ($_GET['success'] === 'activated') echo "Hospital activated successfully!";
                    elseif ($_GET['success'] === 'deleted') echo "Hospital deleted successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">🏥</div>
                    <div class="stat-details">
                        <h3><?php echo $total_hospitals; ?></h3>
                        <p>Total Hospitals</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $active_hospitals; ?></h3>
                        <p>Active Hospitals</p>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <section class="dashboard-section">
                <h2>Search & Filter Hospitals</h2>
                <form method="GET" action="" style="display: grid; grid-template-columns: 2fr 1fr 100px; gap: 15px;">
                    <input type="text" name="search" placeholder="Search by hospital name, username, or email" value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <input type="text" name="city" placeholder="City" value="<?php echo htmlspecialchars($city_filter); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </section>

            <!-- Hospitals List -->
            <section class="dashboard-section">
                <h2>All Hospitals (<?php echo $hospitals->num_rows; ?> results)</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hospital Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>City</th>
                                <th>Total Requests</th>
                                <th>Pending Requests</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($hospitals->num_rows > 0): ?>
                                <?php while ($hospital = $hospitals->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $hospital['hospital_id']; ?></td>
                                        <td><?php echo htmlspecialchars($hospital['hospital_name']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['username']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['email']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($hospital['city']); ?></td>
                                        <td><?php echo $hospital['total_requests']; ?></td>
                                        <td><?php echo $hospital['pending_requests']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $hospital['is_active'] ? 'status-completed' : 'status-cancelled'; ?>">
                                                <?php echo $hospital['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 5px;">
                                                <?php if ($hospital['is_active']): ?>
                                                    <a href="?action=deactivate&id=<?php echo $hospital['hospital_id']; ?>" onclick="return confirm('Deactivate this hospital?')" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">Deactivate</a>
                                                <?php else: ?>
                                                    <a href="?action=activate&id=<?php echo $hospital['hospital_id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Activate</a>
                                                <?php endif; ?>
                                                <a href="?action=delete&id=<?php echo $hospital['hospital_id']; ?>" onclick="return confirm('Delete this hospital permanently?')" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Delete</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 30px;">No hospitals found</td>
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
