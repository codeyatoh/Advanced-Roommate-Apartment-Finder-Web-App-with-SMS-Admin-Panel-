<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Monitoring - RoomFinder Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/admin.module.css">
</head>
<body>
    <?php
    // Start session and load models
    session_start();
    require_once __DIR__ . '/../../models/User.php';
    
    $userModel = new User();
    
    // Get notification statistics from database
    $today = date('Y-m-d');
    $conn = $userModel->getConnection();
    
    // Initialize default values
    $emailsSentToday = 0;
    $smsSentToday = 0;
    $failedDeliveries = 0;
    $pendingQueue = 0;
    $notifications = [];
    
    try {
        // Count emails sent today
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE DATE(created_at) = :today AND type = 'email'";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':today', $today);
        $stmt->execute();
        $result = $stmt->fetch();
        $emailsSentToday = $result['count'] ?? 0;
        
        // Count SMS sent today (no SMS in current schema - set to 0)
        $smsSentToday = 0;
        
        // Count failed deliveries
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE status = 'failed'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $failedDeliveries = $result['count'] ?? 0;
        
        // Count pending notifications
        $sql = "SELECT COUNT(*) as count FROM notifications WHERE status = 'pending'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        $pendingQueue = $result['count'] ?? 0;
        
        // Get all notifications with user details
        $sql = "SELECT n.*, 
                    u.first_name, u.last_name, u.email as user_email, u.phone
                FROM notifications n
                LEFT JOIN users u ON n.user_id = u.user_id
                ORDER BY n.created_at DESC
                LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $notificationsData = $stmt->fetchAll();
        
        // Helper function for time ago
        function notif_time_ago($datetime) {
            $time = strtotime($datetime);
            $now = time();
            $diff = $now - $time;
            
            if ($diff < 60) return 'just now';
            if ($diff < 3600) {
                $mins = floor($diff/60);
                return $mins . ' ' . ($mins == 1 ? 'minute' : 'minutes') . ' ago';
            }
            if ($diff < 86400) {
                $hours = floor($diff/3600);
                return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
            }
            $days = floor($diff/86400);
            return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
        }
        
        // Format notification data
        foreach ($notificationsData as $notifData) {
            $recipientName = ($notifData['first_name'] ?? 'Unknown') . ' ' . ($notifData['last_name'] ?? 'User');
            $recipient = $notifData['type'] === 'email' ? ($notifData['user_email'] ?? 'N/A') : ($notifData['phone'] ?? 'N/A');
            
            $notifications[] = [
                'id' => $notifData['notification_id'],
                'type' => $notifData['type'] ?? 'email',
                'recipient' => $recipient,
                'recipientName' => $recipientName,
                'subject' => $notifData['message'] ?? 'No subject',
                'status' => $notifData['status'],
                'sentAt' => notif_time_ago($notifData['created_at']),
                'deliveredAt' => ($notifData['status'] === 'sent' || $notifData['status'] === 'delivered') ? notif_time_ago($notifData['created_at']) : null,
                'error' => $notifData['status'] === 'failed' ? 'Delivery failed' : null,
            ];
        }
    } catch (PDOException $e) {
        // Table doesn't exist yet - use default values (all zeros)
        error_log("Notifications table not found: " . $e->getMessage());
        // Stats already initialized to 0 above
        // $notifications already initialized to empty array above
    }
    ?>
    <div class="admin-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="admin-container">
            <!-- Header -->
            <div class="page-header animate-slide-up">
                <h1 class="page-title">Notification Monitoring</h1>
                <p class="page-subtitle">Track email and SMS delivery status</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.1s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #3b82f6;">
                            <i data-lucide="mail" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo $emailsSentToday; ?></p>
                    <p class="stat-label">Emails Sent Today</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.2s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #22c55e;">
                            <i data-lucide="message-square" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo $smsSentToday; ?></p>
                    <p class="stat-label">SMS Sent Today</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.3s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #ef4444;">
                            <i data-lucide="x-circle" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo $failedDeliveries; ?></p>
                    <p class="stat-label">Failed Deliveries</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.4s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #eab308;">
                            <i data-lucide="clock" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo $pendingQueue; ?></p>
                    <p class="stat-label">Pending Queue</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="glass-card animate-slide-up" style="padding: 1rem; margin-bottom: 1.5rem; background: transparent; border: none; box-shadow: none;">
                <div class="search-bar-container">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" class="search-input-clean" placeholder="Search notifications...">
                    </div>
                    <div class="search-actions">
                        <button class="btn-filters" onclick="document.getElementById('filterOptions').style.display = document.getElementById('filterOptions').style.display === 'none' ? 'flex' : 'none'">
                            <i data-lucide="sliders-horizontal" style="width: 1rem; height: 1rem;"></i>
                            Filters
                        </button>
                        <button class="btn-search">
                            Search
                        </button>
                    </div>
                </div>
                
                <!-- Expanded Filters -->
                <div id="filterOptions" style="display: none; gap: 1rem; margin-top: 1rem; padding: 1rem; background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border-radius: 1rem;">
                    <select class="form-select-sm" style="flex: 1;">
                        <option>All Types</option>
                        <option>Email</option>
                        <option>SMS</option>
                    </select>
                    <select class="form-select-sm" style="flex: 1;">
                        <option>All Status</option>
                        <option>Delivered</option>
                        <option>Failed</option>
                        <option>Pending</option>
                    </select>
                </div>
            </div>

            <!-- Notifications Table -->
            <div class="glass-card animate-slide-up" style="padding: 0; overflow: hidden;">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Recipient</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Delivered At</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Notifications already loaded from database above
                            foreach ($notifications as $notification): 
                                $statusClass = '';
                                $statusIcon = '';
                                switch ($notification['status']) {
                                    case 'delivered': 
                                        $statusClass = 'status-success'; 
                                        $statusIcon = 'check-circle';
                                        break;
                                    case 'failed': 
                                        $statusClass = 'status-error'; 
                                        $statusIcon = 'x-circle';
                                        break;
                                    case 'pending': 
                                        $statusClass = 'status-warning'; 
                                        $statusIcon = 'clock';
                                        break;
                                }
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <?php if ($notification['type'] === 'email'): ?>
                                            <i data-lucide="mail" style="width: 1.25rem; height: 1.25rem; color: #2563eb;"></i>
                                        <?php else: ?>
                                            <i data-lucide="message-square" style="width: 1.25rem; height: 1.25rem; color: #16a34a;"></i>
                                        <?php endif; ?>
                                        <span style="font-size: 0.875rem; font-weight: 600; color: #000; text-transform: capitalize;"><?php echo $notification['type']; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <p style="font-size: 0.875rem; font-weight: 600; color: #000;"><?php echo $notification['recipientName']; ?></p>
                                        <p style="font-size: 0.75rem; color: rgba(0,0,0,0.6);"><?php echo $notification['recipient']; ?></p>
                                    </div>
                                </td>
                                <td>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);"><?php echo $notification['subject']; ?></p>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <i data-lucide="<?php echo $statusIcon; ?>" style="width: 1rem; height: 1rem; <?php echo $notification['status'] === 'failed' ? 'color: #dc2626;' : ($notification['status'] === 'pending' ? 'color: #ca8a04;' : 'color: #16a34a;'); ?>"></i>
                                        <span class="status-badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($notification['status']); ?>
                                        </span>
                                    </div>
                                    <?php if (isset($notification['error'])): ?>
                                        <p style="font-size: 0.75rem; color: #dc2626; margin-top: 0.25rem;"><?php echo $notification['error']; ?></p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);"><?php echo $notification['sentAt']; ?></p>
                                </td>
                                <td>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);"><?php echo isset($notification['deliveredAt']) ? $notification['deliveredAt'] : '-'; ?></p>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                        <?php if ($notification['status'] === 'failed'): ?>
                                            <button class="btn btn-primary btn-sm">
                                                <i data-lucide="send" style="width: 1rem; height: 1rem;"></i>
                                                Retry
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-ghost btn-sm">View</button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
