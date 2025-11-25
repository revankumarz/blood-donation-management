<?php
require_once 'config.php';
check_login('donor');

$user_id = get_user_id();

// Mark notification as read
if (isset($_GET['read']) && isset($_GET['id'])) {
    $notification_id = intval($_GET['id']);
    $conn->query("UPDATE notifications SET is_read = 1 WHERE notification_id = $notification_id AND user_id = $user_id");
    header("Location: donor_notifications.php");
    exit();
}

// Get donor info
$donor = $conn->query("SELECT * FROM donors WHERE user_id = $user_id")->fetch_assoc();

// Get notifications
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");

// Count unread
$unread_count = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - BloodLife</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">BloodLife Donor</span>
            </div>
            <nav class="sidebar-nav">
                <a href="donor_dashboard.php" class="nav-item">
                    <span>📊</span> Dashboard
                </a>
                <a href="donor_profile.php" class="nav-item">
                    <span>👤</span> My Profile
                </a>
                <a href="donor_appointments.php" class="nav-item">
                    <span>📅</span> Appointments
                </a>
                <a href="donor_history.php" class="nav-item">
                    <span>💉</span> Donation History
                </a>
                <a href="donor_notifications.php" class="nav-item active">
                    <span>🔔</span> Notifications
                    <?php if ($unread_count > 0): ?>
                        <span style="background: #dc3545; color: white; border-radius: 10px; padding: 2px 6px; font-size: 11px; margin-left: 5px;"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="nav-item logout">
                    <span>🚪</span> Logout
                </a>
            </nav>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Notifications</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($donor['full_name']); ?></span>
                </div>
            </header>

            <section class="dashboard-section">
                <h2>All Notifications (<?php echo $unread_count; ?> unread)</h2>

                <?php if ($notifications->num_rows > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php while ($notification = $notifications->fetch_assoc()): ?>
                            <div style="padding: 15px; background: <?php echo $notification['is_read'] ? '#f8f9fa' : '#e3f2fd'; ?>; border-left: 4px solid <?php echo get_urgency_color($notification['type']); ?>; border-radius: 8px; position: relative;">
                                <?php if (!$notification['is_read']): ?>
                                    <span style="position: absolute; top: 10px; right: 10px; background: #2196f3; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px;">NEW</span>
                                <?php endif; ?>

                                <h4 style="margin: 0 0 8px 0; color: #333;">
                                    <?php
                                    $icon = ['info' => 'ℹ️', 'warning' => '⚠️', 'urgent' => '🚨', 'success' => '✅'][$notification['type']] ?? 'ℹ️';
                                    echo $icon . ' ' . htmlspecialchars($notification['title']);
                                    ?>
                                </h4>
                                <p style="margin: 0 0 8px 0; color: #666;"><?php echo htmlspecialchars($notification['message']); ?></p>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <small style="color: #999;"><?php echo format_datetime($notification['created_at']); ?></small>
                                    <?php if (!$notification['is_read']): ?>
                                        <a href="?read=1&id=<?php echo $notification['notification_id']; ?>" style="color: #667eea; font-size: 12px; text-decoration: none;">Mark as read</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 50px; color: #999;">
                        <div style="font-size: 48px; margin-bottom: 20px;">🔔</div>
                        <p>No notifications yet</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
