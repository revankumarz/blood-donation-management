<?php
require_once 'config.php';
check_login('admin');

// Handle add donation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_donation'])) {
    $donor_id = intval($_POST['donor_id']);
    $donation_date = sanitize_input($_POST['donation_date']);
    $quantity_ml = intval($_POST['quantity_ml']);
    $blood_type = sanitize_input($_POST['blood_type']);
    $status = sanitize_input($_POST['status']);

    $stmt = $conn->prepare("INSERT INTO donations (donor_id, donation_date, quantity_ml, blood_type, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $donor_id, $donation_date, $quantity_ml, $blood_type, $status);

    if ($stmt->execute()) {
        // Update donor stats
        $conn->query("UPDATE donors SET total_donations = total_donations + 1, last_donation_date = '$donation_date' WHERE donor_id = $donor_id");

        // Update inventory if completed
        if ($status === 'completed') {
            update_blood_inventory($blood_type, $quantity_ml);
        }

        log_action(get_user_id(), "DONATION_ADDED", "Donation added for donor ID: $donor_id");
        header("Location: manage_donations.php?success=added");
        exit();
    }
    $stmt->close();
}

// Handle update donation status
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $donation_id = intval($_GET['id']);
    $new_status = $_GET['update_status'];

    if (in_array($new_status, ['completed', 'cancelled'])) {
        $donation = $conn->query("SELECT * FROM donations WHERE donation_id = $donation_id")->fetch_assoc();

        if ($new_status === 'completed' && $donation['status'] !== 'completed') {
            update_blood_inventory($donation['blood_type'], $donation['quantity_ml']);
        }

        $stmt = $conn->prepare("UPDATE donations SET status = ? WHERE donation_id = ?");
        $stmt->bind_param("si", $new_status, $donation_id);
        $stmt->execute();
        $stmt->close();

        header("Location: manage_donations.php?success=updated");
        exit();
    }
}

// Get donations
$donations = $conn->query("SELECT d.*, don.full_name as donor_name, h.hospital_name
    FROM donations d
    JOIN donors don ON d.donor_id = don.donor_id
    LEFT JOIN hospitals h ON d.hospital_id = h.hospital_id
    ORDER BY d.donation_date DESC
    LIMIT 100");

// Get donors for dropdown
$donors = $conn->query("SELECT donor_id, full_name, blood_type FROM donors ORDER BY full_name");

// Statistics
$total_donations = $conn->query("SELECT COUNT(*) as count FROM donations")->fetch_assoc()['count'];
$completed_donations = $conn->query("SELECT COUNT(*) as count FROM donations WHERE status = 'completed'")->fetch_assoc()['count'];
$total_blood_collected = $conn->query("SELECT SUM(quantity_ml) as total FROM donations WHERE status = 'completed'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Donations - BloodLife</title>
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
                <a href="manage_donations.php" class="nav-item active">
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
                <h1>Manage Donations</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </header>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php
                    if ($_GET['success'] === 'added') echo "Donation added successfully!";
                    elseif ($_GET['success'] === 'updated') echo "Donation status updated successfully!";
                    ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4CAF50;">💉</div>
                    <div class="stat-details">
                        <h3><?php echo $total_donations; ?></h3>
                        <p>Total Donations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2196F3;">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $completed_donations; ?></h3>
                        <p>Completed Donations</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #FF5722;">🩸</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($total_blood_collected); ?> ml</h3>
                        <p>Total Blood Collected</p>
                    </div>
                </div>
            </div>

            <!-- Add Donation Form -->
            <section class="dashboard-section">
                <h2>Add New Donation</h2>
                <form method="POST" action="" style="max-width: 800px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="donor_id">Select Donor</label>
                            <select id="donor_id" name="donor_id" required onchange="updateBloodType()">
                                <option value="">Choose a donor...</option>
                                <?php while ($donor = $donors->fetch_assoc()): ?>
                                    <option value="<?php echo $donor['donor_id']; ?>" data-bloodtype="<?php echo $donor['blood_type']; ?>">
                                        <?php echo htmlspecialchars($donor['full_name']); ?> (<?php echo $donor['blood_type']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="blood_type">Blood Type</label>
                            <select id="blood_type" name="blood_type" required>
                                <option value="">Select blood type</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type): ?>
                                    <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="donation_date">Donation Date</label>
                            <input type="date" id="donation_date" name="donation_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="quantity_ml">Quantity (ml)</label>
                            <input type="number" id="quantity_ml" name="quantity_ml" required value="450" min="100" step="50">
                        </div>

                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" name="add_donation" class="btn btn-primary">Add Donation</button>
                </form>
            </section>

            <!-- Donations List -->
            <section class="dashboard-section">
                <h2>Recent Donations</h2>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Donor Name</th>
                                <th>Blood Type</th>
                                <th>Date</th>
                                <th>Quantity</th>
                                <th>Hospital</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($donations->num_rows > 0): ?>
                                <?php while ($donation = $donations->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $donation['donation_id']; ?></td>
                                        <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                                        <td><span class="badge"><?php echo $donation['blood_type']; ?></span></td>
                                        <td><?php echo format_date($donation['donation_date']); ?></td>
                                        <td><?php echo $donation['quantity_ml']; ?> ml</td>
                                        <td><?php echo $donation['hospital_name'] ?? 'N/A'; ?></td>
                                        <td><span class="status-badge status-<?php echo $donation['status']; ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                        <td>
                                            <?php if ($donation['status'] === 'pending'): ?>
                                                <a href="?update_status=completed&id=<?php echo $donation['donation_id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">Complete</a>
                                                <a href="?update_status=cancelled&id=<?php echo $donation['donation_id']; ?>" onclick="return confirm('Cancel this donation?')" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">Cancel</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 30px;">No donations found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        function updateBloodType() {
            const donorSelect = document.getElementById('donor_id');
            const bloodTypeSelect = document.getElementById('blood_type');
            const selectedOption = donorSelect.options[donorSelect.selectedIndex];
            const bloodType = selectedOption.getAttribute('data-bloodtype');

            if (bloodType) {
                bloodTypeSelect.value = bloodType;
            }
        }
    </script>
</body>
</html>
