<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Management - RoomFinder Admin</title>
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
    require_once __DIR__ . '/../../models/Report.php';
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../models/Listing.php';
    
    $reportModel = new Report();
    $userModel = new User();
    $listingModel = new Listing();
    
    // Get report statistics
    $reportStats = $reportModel->getStats();
    
    // Get all reports from database
    $sql = "SELECT r.*, 
                reporter.first_name as reporter_first, reporter.last_name as reporter_last, reporter.profile_photo as reporter_photo,
                reported_user.first_name as reported_first, reported_user.last_name as reported_last,
                l.title as listing_title
            FROM reports r
            LEFT JOIN users reporter ON r.reporter_id = reporter.user_id
            LEFT JOIN users reported_user ON r.reported_user_id = reported_user.user_id
            LEFT JOIN listings l ON r.reported_listing_id = l.listing_id
            ORDER BY r.created_at DESC";
    $stmt = $reportModel->getConnection()->prepare($sql);
    $stmt->execute();
    $reportsData = $stmt->fetchAll();
    
    // Helper function for time ago
    function report_time_ago($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) return 'just now';
        if ($diff < 3600) {
            $mins = floor($diff/60);
            return $mins . ' ' . ($mins == 1 ? 'hour' : 'hours') . ' ago';
        }
        if ($diff < 86400) {
            $hours = floor($diff/3600);
            return $hours . ' ' . ($hours == 1 ? 'hour' : 'hours') . ' ago';
        }
        $days = floor($diff/86400);
        return $days . ' ' . ($days == 1 ? 'day' : 'days') . ' ago';
    }
    
    // Format report data  
    $reports = [];
    foreach ($reportsData as $reportData) {
        $reporterName = ($reportData['reporter_first'] ?? 'Unknown') . ' ' . ($reportData['reporter_last'] ?? 'User');
        $reportedUserName = ($reportData['reported_first'] ?? 'Unknown') . ' ' . ($reportData['reported_last'] ?? 'User');
        
        // Determine what was reported
        $reported = '';
        if ($reportData['report_type'] === 'listing' && $reportData['listing_title']) {
            $reported = $reportData['listing_title'];
        } elseif ($reportData['report_type'] === 'user') {
            $reported = $reportedUserName;
        } else {
            $reported = 'Message/Content';
        }
        
        $reports[] = [
            'id' => $reportData['report_id'],
            'type' => $reportData['report_type'],
            'category' => $reportData['reason'] ?? 'General Report',
            'reporter' => $reporterName,
            'reporterAvatar' => $reportData['reporter_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($reporterName) . '&background=3b82f6&color=fff',
            'reported' => $reported,
            'reportedUser' => $reportedUserName,
            'description' => $reportData['description'] ?? 'No description provided.',
            'status' => $reportData['status'],
            'priority' => 'medium', // Default priority as it's not in schema
            'submittedDate' => report_time_ago($reportData['created_at']),
        ];
    }
    
    // Count high priority (pending) reports
    $highPriorityCount = 0;
    foreach ($reports as $report) {
        if ($report['status'] === 'pending') {
            $highPriorityCount++;
        }
    }
    ?>
    <div class="admin-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="admin-container">
            <!-- Header -->
            <div class="page-header animate-slide-up">
                <h1 class="page-title">Reports Management</h1>
                <p class="page-subtitle">Review and resolve user complaints and reports</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.1s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #eab308;">
                            <i data-lucide="clock" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo intval($reportStats['pending_reports'] ?? 0); ?></p>
                    <p class="stat-label">Pending</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.2s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #3b82f6;">
                            <i data-lucide="alert-triangle" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value">0</p>
                    <p class="stat-label">Investigating</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.3s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #22c55e;">
                            <i data-lucide="check-circle" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo intval($reportStats['resolved_reports'] ?? 0); ?></p>
                    <p class="stat-label">Resolved</p>
                </div>

                <div class="glass-card stat-card animate-slide-up" style="animation-delay: 0.4s;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div class="stat-icon-wrapper" style="background-color: #ef4444;">
                            <i data-lucide="x-circle" class="stat-icon"></i>
                        </div>
                    </div>
                    <p class="stat-value"><?php echo $highPriorityCount; ?></p>
                    <p class="stat-label">High Priority</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="glass-card animate-slide-up" style="padding: 1rem; margin-bottom: 1.5rem; background: transparent; border: none; box-shadow: none;">
                <div class="search-bar-container">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" class="search-input-clean" placeholder="Search reports...">
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
                        <option>Listing Reports</option>
                        <option>User Reports</option>
                        <option>Message Reports</option>
                    </select>
                    <select class="form-select-sm" style="flex: 1;">
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Investigating</option>
                        <option>Resolved</option>
                    </select>
                </div>
            </div>

            <!-- Reports List -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php
                // Reports already loaded from database above
                foreach ($reports as $index => $report): 
                    $typeIcon = '';
                    switch ($report['type']) {
                        case 'listing': $typeIcon = 'home'; break;
                        case 'user': $typeIcon = 'user'; break;
                        case 'message': $typeIcon = 'message-square'; break;
                        default: $typeIcon = 'alert-triangle';
                    }

                    $statusClass = '';
                    switch ($report['status']) {
                        case 'pending': $statusClass = 'status-warning'; break;
                        case 'investigating': $statusClass = 'status-info'; break;
                        case 'resolved': $statusClass = 'status-success'; break;
                        default: $statusClass = 'status-neutral';
                    }

                    $priorityClass = '';
                    switch ($report['priority']) {
                        case 'high': $priorityClass = 'priority-high'; break;
                        case 'medium': $priorityClass = 'priority-medium'; break;
                        default: $priorityClass = 'status-success';
                    }
                ?>
                <div class="glass-card animate-slide-up" style="padding: 1.25rem; animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="listing-card-content">
                        <!-- Icon -->
                        <div style="flex-shrink: 0;">
                            <div style="width: 3rem; height: 3rem; background-color: #fee2e2; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center;">
                                <i data-lucide="<?php echo $typeIcon; ?>" style="width: 1.5rem; height: 1.5rem; color: #dc2626;"></i>
                            </div>
                        </div>

                        <!-- Content -->
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                        <h3 style="font-size: 1.125rem; font-weight: 700; color: #000;">Report #<?php echo $report['id']; ?></h3>
                                        <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo ucfirst($report['priority']); ?></span>
                                        <span class="status-badge <?php echo $statusClass; ?>"><?php echo ucfirst($report['status']); ?></span>
                                    </div>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7); margin-bottom: 0.25rem;">
                                        <span style="font-weight: 600;">Category:</span> <?php echo $report['category']; ?>
                                    </p>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);">
                                        <span style="font-weight: 600;">Reported:</span> <?php echo $report['reported']; ?> by <?php echo $report['reportedUser']; ?>
                                    </p>
                                </div>
                                <p style="font-size: 0.75rem; color: rgba(0,0,0,0.5); flex-shrink: 0;"><?php echo $report['submittedDate']; ?></p>
                            </div>

                            <!-- Reporter Info -->
                            <div class="glass-subtle" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; padding: 0.75rem;">
                                <img src="<?php echo $report['reporterAvatar']; ?>" alt="<?php echo $report['reporter']; ?>" style="width: 2rem; height: 2rem; border-radius: 9999px; object-fit: cover;">
                                <div>
                                    <p style="font-size: 0.875rem; font-weight: 600; color: #000;">Reported by <?php echo $report['reporter']; ?></p>
                                </div>
                            </div>

                            <!-- Description -->
                            <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7); margin-bottom: 1rem; padding: 0.75rem; background-color: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                <?php echo $report['description']; ?>
                            </p>

                            <!-- Actions -->
                            <?php if ($report['status'] === 'pending'): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary btn-sm">
                                    <i data-lucide="alert-triangle" style="width: 1rem; height: 1rem;"></i>
                                    Start Investigation
                                </button>
                                <button class="btn btn-glass btn-sm">View Details</button>
                                <button class="btn btn-ghost btn-sm" style="color: #dc2626;">Dismiss</button>
                            </div>
                            <?php elseif ($report['status'] === 'investigating'): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary btn-sm">
                                    <i data-lucide="check-circle" style="width: 1rem; height: 1rem;"></i>
                                    Mark Resolved
                                </button>
                                <button class="btn btn-glass btn-sm">Add Note</button>
                            </div>
                            <?php elseif ($report['status'] === 'resolved'): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #15803d;">
                                <i data-lucide="check-circle" style="width: 1rem; height: 1rem;"></i>
                                <span>Report resolved and user notified</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
