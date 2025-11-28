<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewing Appointments - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/landlord.module.css">
</head>
<body>
<?php
// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Start session and load models
session_start();
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Listing.php';

// Check if user is logged in as landlord
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: /Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/public/login.php');
    exit;
}

$landlordId = $_SESSION['user_id'];
$appointmentModel = new Appointment();
$userModel = new User();
$listingModel = new Listing();

// Get all appointments for this landlord's listings
$appointments = $appointmentModel->getLandlordAppointments($landlordId);

// Format appointment data
foreach ($appointments as &$appt) {
    // Get tenant details
    $tenant = $userModel->getById($appt['seeker_id']);
    if ($tenant && is_array($tenant)) {
        $appt['tenant'] = $tenant['first_name'] . ' ' . $tenant['last_name'];
        $appt['email'] = $tenant['email'] ?? '';
        $appt['phone'] = $tenant['phone'] ?? '';
        $appt['tenantAvatar'] = $tenant['profile_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($appt['tenant']) . '&background=3b82f6&color=fff';
    } else {
        $appt['tenant'] = 'Deleted User';
        $appt['email'] = '';
        $appt['phone'] = '';
        $appt['tenantAvatar'] = 'https://ui-avatars.com/api/?name=DeletedUser&background=ef4444&color=fff';
    }
    
    // Get listing details
    $listing = $listingModel->getById($appt['listing_id']);
    if ($listing && is_array($listing)) {
        $appt['property'] = $listing['title'];
        $appt['location'] = $listing['address'] . ', ' . $listing['city'];
        // Get first image
        if (!empty($listing['images'])) {
            $images = is_array($listing['images']) ? $listing['images'] : json_decode($listing['images'], true);
            $appt['propertyImage'] = $images[0] ?? 'https://via.placeholder.com/400x300?text=No+Image';
        } else {
            $appt['propertyImage'] = 'https://via.placeholder.com/400x300?text=No+Image';
        }
    } else {
        $appt['property'] = 'Deleted Listing';
        $appt['location'] = 'N/A';
        $appt['propertyImage'] = 'https://via.placeholder.com/400x300?text=Deleted';
    }
    
    // Format date
    $apptDate = new DateTime($appt['appointment_date'] . ' ' . $appt['appointment_time']);
    $now = new DateTime();
    $tomorrow = new DateTime('tomorrow');
    
    if ($apptDate->format('Y-m-d') === $now->format('Y-m-d')) {
        $appt['date'] = 'Today';
    } elseif ($apptDate->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
        $appt['date'] = 'Tomorrow';
    } else {
        $appt['date'] = $apptDate->format('M j, Y');
    }
    
    // Format time
    $appt['time'] = $apptDate->format('g:i A');
    
    // Format requested date
    $created = new DateTime($appt['created_at']);
    $diff = $now->diff($created);
    if ($diff->days > 0) {
        $appt['requestedDate'] = $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
    } elseif ($diff->h > 0) {
        $appt['requestedDate'] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    } else {
        $appt['requestedDate'] = max(1, $diff->i) . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
}
?>
    <div class="landlord-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="landlord-container-wide">
            <!-- Header -->
            <div class="page-header animate-slide-up">
                <div>
                    <h1 class="page-title">Viewing Appointments</h1>
                    <p class="page-subtitle">Manage property viewing requests from potential tenants</p>
                </div>
            </div>

            <!-- Appointments List -->
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <?php
                // Appointments already loaded from database at top of file
                if (empty($appointments)):
                ?>
                <div class="glass-card" style="padding: 4rem 2rem; text-align: center;">
                    <i data-lucide="calendar-x" style="width: 4rem; height: 4rem; color: rgba(0,0,0,0.2); margin: 0 auto 1rem;"></i>
                    <h3 style="color: rgba(0,0,0,0.6); margin: 0 0 0.5rem 0;">No appointments yet</h3>
                    <p style="color: rgba(0,0,0,0.5); margin: 0;">Appointment requests will appear here</p>
                </div>
                <?php
                endif;

                foreach ($appointments as $index => $appointment): 
                    $statusColor = '';
                    switch ($appointment['status']) {
                        case 'confirmed': $statusColor = 'color: #15803d; background-color: #dcfce7;'; break;
                        case 'pending': $statusColor = 'color: #a16207; background-color: #fef9c3;'; break;
                        case 'declined': $statusColor = 'color: #b91c1c; background-color: #fee2e2;'; break;
                        default: $statusColor = 'color: rgba(0,0,0,0.6); background-color: rgba(0,0,0,0.1);';
                    }
                ?>
                <div class="glass-card animate-slide-up" style="padding: 1.25rem; animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="appointment-card">
                        <!-- Property Image -->
                        <div style="flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                            <img src="<?php echo $appointment['propertyImage']; ?>" alt="<?php echo $appointment['property']; ?>" class="appointment-image">
                        </div>

                        <!-- Content -->
                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; margin-bottom: 0.75rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <h3 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 0.25rem;"><?php echo $appointment['property']; ?></h3>
                                    <div style="display: flex; align-items: center; gap: 0.375rem; font-size: 0.875rem; color: rgba(0,0,0,0.6);">
                                        <i data-lucide="map-pin" style="width: 0.875rem; height: 0.875rem; flex-shrink: 0;"></i>
                                        <span><?php echo $appointment['location']; ?></span>
                                    </div>
                                </div>
                                <span style="padding: 0.25rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; flex-shrink: 0; <?php echo $statusColor; ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </div>

                            <!-- Tenant Info -->
                            <div class="glass-subtle" style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; padding: 0.75rem; border-radius: 0.75rem;">
                                <img src="<?php echo $appointment['tenantAvatar']; ?>" alt="<?php echo $appointment['tenant']; ?>" style="width: 2.5rem; height: 2.5rem; border-radius: 9999px; object-fit: cover;">
                                <div style="flex: 1; min-width: 0;">
                                    <p style="font-weight: 600; font-size: 0.875rem; color: #000;"><?php echo $appointment['tenant']; ?></p>
                                    <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.75rem; color: rgba(0,0,0,0.6);">
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <i data-lucide="mail" style="width: 0.75rem; height: 0.75rem;"></i>
                                            <?php echo $appointment['email']; ?>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.25rem;">
                                            <i data-lucide="phone" style="width: 0.75rem; height: 0.75rem;"></i>
                                            <?php echo $appointment['phone']; ?>
                                        </div>
                                    </div>
                                </div>
                                <p style="font-size: 0.75rem; color: rgba(0,0,0,0.5); flex-shrink: 0;">Requested <?php echo $appointment['requestedDate']; ?></p>
                            </div>

                            <!-- Appointment Details -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center; gap: 0.625rem;">
                                    <div style="width: 2rem; height: 2rem; background-color: rgba(30, 58, 138, 0.2); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i data-lucide="calendar" style="width: 1rem; height: 1rem; color: var(--deep-blue);"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <p style="font-size: 0.75rem; color: rgba(0,0,0,0.5);">Date</p>
                                        <p style="font-size: 0.875rem; font-weight: 600; color: #000;"><?php echo $appointment['date']; ?></p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.625rem;">
                                    <div style="width: 2rem; height: 2rem; background-color: rgba(30, 58, 138, 0.2); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i data-lucide="clock" style="width: 1rem; height: 1rem; color: var(--deep-blue);"></i>
                                    </div>
                                    <div style="min-width: 0;">
                                        <p style="font-size: 0.75rem; color: rgba(0,0,0,0.5);">Time</p>
                                        <p style="font-size: 0.875rem; font-weight: 600; color: #000;"><?php echo $appointment['time']; ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <?php if ($appointment['status'] === 'pending'): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary btn-sm" style="flex: 1;" onclick="handleApprove(<?php echo $appointment['id']; ?>)">
                                    <i data-lucide="check" style="width: 1rem; height: 1rem;"></i>
                                    Approve & Notify
                                </button>
                                <button class="btn btn-ghost btn-sm" style="flex: 1;" onclick="handleDecline(<?php echo $appointment['id']; ?>)">
                                    <i data-lucide="x" style="width: 1rem; height: 1rem;"></i>
                                    Decline
                                </button>
                            </div>
                            <?php elseif ($appointment['status'] === 'confirmed'): ?>
                            <div style="display: flex; gap: 0.75rem;">
                                <button class="btn btn-glass btn-sm" style="flex: 1; font-size: 0.875rem;">Reschedule</button>
                                <button class="btn btn-ghost btn-sm" style="flex: 1; font-size: 0.875rem;">Cancel</button>
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

        function handleApprove(id) {
            console.log('Approve appointment:', id);
            // Add approval logic here
        }

        function handleDecline(id) {
            console.log('Decline appointment:', id);
            // Add decline logic here
        }
    </script>
</body>
</html>
