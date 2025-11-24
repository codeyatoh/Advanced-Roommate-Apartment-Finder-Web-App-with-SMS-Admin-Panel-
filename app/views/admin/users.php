<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - RoomFinder Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/admin.module.css">
</head>
<\u003cbody>
    <?php
    // Start session and load models
    session_start();
    require_once __DIR__ . '/../../models/User.php';
    require_once __DIR__ . '/../../models/Listing.php';
    require_once __DIR__ . '/../../models/Report.php';
    
    $userModel = new User();
    $listingModel = new Listing();
    $reportModel = new Report();
    
    // Get all users from database
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $userModel->getConnection()->prepare($sql);
    $stmt->execute();
    $usersData = $stmt->fetchAll();
    
    // Format user data with additional stats
    $users = [];
    foreach ($usersData as $userData) {
        // Get listing count if landlord
        $listingCount = 0;
        if ($userData['role'] === 'landlord') {
            $sql = "SELECT COUNT(*) as count FROM listings WHERE landlord_id = :user_id";
            $stmt = $listingModel->getConnection()->prepare($sql);
            $stmt->bindValue(':user_id', $userData['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $listingCount = $result['count'] ?? 0;
        }
        
        // Get report count (reported by others)
        $sql = "SELECT COUNT(*) as count FROM reports WHERE reported_user_id = :user_id";
        $stmt = $reportModel->getConnection()->prepare($sql);
        $stmt->bindValue(':user_id', $userData['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $reportCount = $result['count'] ?? 0;
        
        // Format data
        $users[] = [
            'id' => $userData['user_id'],
            'name' => $userData['first_name'] . ' ' . $userData['last_name'],
            'email' => $userData['email'],
            'phone' => $userData['phone'] ?? 'N/A',
            'role' => $userData['role'],
            'status' => $userData['is_verified'] ? 'verified' : ($userData['is_active'] ? 'pending' : 'banned'),
            'joinDate' => date('M j, Y', strtotime($userData['created_at'])),
            'avatar' => $userData['profile_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($userData['first_name'] . ' ' . $userData['last_name']) . '&background=10b981&color=fff',
            'listings' => $listingCount,
            'reports' => $reportCount,
        ];
    }
    ?>
    <div class="admin-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="admin-container">
            <!-- Header -->
            <div class="page-header animate-slide-up">
                <h1 class="page-title">User Management</h1>
                <p class="page-subtitle">Manage user accounts and permissions</p>
            </div>

            <!-- Search & Filters -->
            <div class="glass-card animate-slide-up" style="padding: 1rem; margin-bottom: 1.5rem; background: transparent; border: none; box-shadow: none;">
                <div class="search-bar-container">
                    <div class="search-input-wrapper">
                        <i data-lucide="search" class="search-icon"></i>
                        <input type="text" class="search-input-clean" placeholder="Search users...">
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
                        <option>All Roles</option>
                        <option>Seekers</option>
                        <option>Landlords</option>
                    </select>
                    <select class="form-select-sm" style="flex: 1;">
                        <option>All Status</option>
                        <option>Verified</option>
                        <option>Pending</option>
                        <option>Banned</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="glass-card animate-slide-up" style="padding: 0; overflow: hidden;">
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Activity</th>
                                <th>Joined</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Users already loaded from database above
                            foreach ($users as $user): 
                                $statusClass = '';
                                switch ($user['status']) {
                                    case 'verified': $statusClass = 'status-success'; break;
                                    case 'pending': $statusClass = 'status-warning'; break;
                                    case 'banned': $statusClass = 'status-error'; break;
                                }

                                $roleClass = $user['role'] === 'landlord' ? 'background-color: #dbeafe; color: #1d4ed8;' : 'background-color: #f3e8ff; color: #7e22ce;';
                            ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <img src="<?php echo $user['avatar']; ?>" alt="<?php echo $user['name']; ?>" style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; object-fit: cover;">
                                        <div>
                                            <p style="font-size: 0.875rem; font-weight: 600; color: #000;"><?php echo $user['name']; ?></p>
                                            <p style="font-size: 0.75rem; color: rgba(0,0,0,0.6);">ID: <?php echo $user['id']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: rgba(0,0,0,0.7);">
                                            <i data-lucide="mail" style="width: 0.75rem; height: 0.75rem;"></i>
                                            <?php echo $user['email']; ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: rgba(0,0,0,0.7);">
                                            <i data-lucide="phone" style="width: 0.75rem; height: 0.75rem;"></i>
                                            <?php echo $user['phone']; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; <?php echo $roleClass; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-size: 0.75rem; color: rgba(0,0,0,0.7);">
                                        <p><?php echo $user['listings']; ?> listings</p>
                                        <p style="color: #dc2626;"><?php echo $user['reports']; ?> reports</p>
                                    </div>
                                </td>
                                <td>
                                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.7);"><?php echo $user['joinDate']; ?></p>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                        <?php if ($user['status'] === 'pending'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="handleVerify(<?php echo $user['id']; ?>)">
                                                <i data-lucide="check-circle" style="width: 1rem; height: 1rem;"></i>
                                                Verify
                                            </button>
                                        <?php elseif ($user['status'] === 'verified'): ?>
                                            <button class="btn btn-ghost btn-sm" onclick="handleEdit(<?php echo $user['id']; ?>)">
                                                <i data-lucide="edit" style="width: 1rem; height: 1rem;"></i>
                                            </button>
                                            <button class="btn btn-ghost btn-sm" style="color: #dc2626;" onclick="handleBan(<?php echo $user['id']; ?>)">
                                                <i data-lucide="ban" style="width: 1rem; height: 1rem;"></i>
                                            </button>
                                        <?php elseif ($user['status'] === 'banned'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="handleVerify(<?php echo $user['id']; ?>)">
                                                Unban
                                            </button>
                                        <?php endif; ?>
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

        function handleVerify(id) {
            console.log('Verify user:', id);
            // Add verify logic here
        }

        function handleBan(id) {
            console.log('Ban user:', id);
            // Add ban logic here
        }

        function handleEdit(id) {
            console.log('Edit user:', id);
            // Add edit logic here
        }
    </script>
</body>
</html>
