<?php
require_once 'config.php';
check_login('hospital');

$user_id = get_user_id();
$hospital = $conn->query("SELECT * FROM hospitals WHERE user_id = $user_id")->fetch_assoc();

// Get search filters
$blood_type_filter = $_GET['blood_type'] ?? '';
$city_filter = $_GET['city'] ?? '';

// Build query
$query = "SELECT d.*, u.email FROM donors d JOIN users u ON d.user_id = u.user_id WHERE u.is_active = 1";

if ($blood_type_filter) {
    $query .= " AND d.blood_type = '$blood_type_filter'";
}

if ($city_filter) {
    $city_filter_escaped = $conn->real_escape_string($city_filter);
    $query .= " AND d.city LIKE '%$city_filter_escaped%'";
}

$query .= " ORDER BY d.is_eligible DESC, d.last_donation_date ASC";
$donors = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Donors - BloodLife</title>
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
                <a href="hospital_inventory.php" class="nav-item">
                    <span>🩸</span> Blood Inventory
                </a>
                <a href="hospital_donors.php" class="nav-item active">
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
                <h1>Find Donors</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($hospital['hospital_name']); ?></span>
                </div>
            </header>

            <!-- Search Filters -->
            <section class="dashboard-section">
                <h2>Search Donors</h2>
                <form method="GET" action="" style="display: grid; grid-template-columns: 1fr 1fr 100px; gap: 15px;">
                    <select name="blood_type" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                        <option value="">All Blood Types</option>
                        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo $blood_type_filter === $type ? 'selected' : ''; ?>><?php echo $type; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" name="city" placeholder="Filter by city" value="<?php echo htmlspecialchars($city_filter); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 8px;">

                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </section>

            <!-- Donors List -->
            <section class="dashboard-section">
                <h2>Available Donors (<?php echo $donors->num_rows; ?> results)</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Blood Type</th>
                                <th>Gender</th>
                                <th>City</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Total Donations</th>
                                <th>Last Donation</th>
                                <th>Eligible</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($donors->num_rows > 0): ?>
                                <?php while ($donor = $donors->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donor['full_name']); ?></td>
                                        <td><span class="badge"><?php echo $donor['blood_type']; ?></span></td>
                                        <td><?php echo ucfirst($donor['gender']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['city']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($donor['email']); ?></td>
                                        <td><?php echo $donor['total_donations']; ?></td>
                                        <td><?php echo $donor['last_donation_date'] ? format_date($donor['last_donation_date']) : 'Never'; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $donor['is_eligible'] ? 'status-completed' : 'status-pending'; ?>">
                                                <?php echo $donor['is_eligible'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 30px;">
                                        No donors found matching your criteria. Try different filters.
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
