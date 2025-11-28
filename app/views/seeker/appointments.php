<?php
// Set timezone to Philippine Time
date_default_timezone_set('Asia/Manila');

// Start session and load models
session_start();
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../models/Listing.php';
require_once __DIR__ . '/../../models/User.php';

// Get current user
$userId = $_SESSION['user_id'] ?? 1; // Fallback for development

// Update last viewed timestamp for appointments badge
$_SESSION['last_viewed_appointments_seeker'] = time();

$appointmentModel = new Appointment();
$listingModel = new Listing();
$userModel = new User();

// Fetch appointments for this seeker
$appointments = $appointmentModel->getUpcoming($userId, 'seeker');

// Group appointments by status
$upcomingAppointments = [];
$pastAppointments = [];
$today = date('Y-m-d');

foreach ($appointments as $apt) {
    if ($apt['status'] === 'completed' || $apt['status'] === 'cancelled' || 
        ($apt['appointment_date'] < $today)) {
        $pastAppointments[] = $apt;
    } else {
        $upcomingAppointments[] = $apt;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - RoomFinder</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/cards.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/forms.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/appointments.module.css">
</head>
<body>
    <div style="min-height: 100vh; background: linear-gradient(to bottom right, var(--softBlue-20), var(--neutral), var(--deepBlue-10));">
        <!-- Navbar -->
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div style="padding-top: 6rem; padding-bottom: 5rem; padding-left: 1rem; padding-right: 1rem;">
            <div style="max-width: 1280px; margin: 0 auto;">
                <!-- Header -->
                <div style="margin-bottom: 2rem; animation: slideUp 0.3s ease-out;">
                    <h1 style="font-size: 1.875rem; font-weight: 700; color: #000000; margin-bottom: 0.5rem;">
                        My Appointments
                    </h1>
                    <p style="color: rgba(0, 0, 0, 0.6);">
                        Manage your property viewing schedule
                    </p>
                </div>

                <!-- Upcoming Appointments -->
                <?php if (!empty($upcomingAppointments)): ?>
                <div style="margin-bottom: 3rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin-bottom: 1.5rem;">Upcoming</h2>
                    <div class="appointments-list">
                        <?php foreach ($upcomingAppointments as $index => $apt): 
                            // Get landlord info
                            $landlord = $userModel->getById($apt['landlord_id']);
                            $landlordName = htmlspecialchars($landlord['first_name'] . ' ' . $landlord['last_name']);
                            
                            // Format date
                            $date = new DateTime($apt['appointment_date']);
                            $today = new DateTime();
                            $tomorrow = new DateTime('tomorrow');
                            
                            if ($date->format('Y-m-d') === $today->format('Y-m-d')) {
                                $dateDisplay = 'Today';
                            } elseif ($date->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                                $dateDisplay = 'Tomorrow';
                            } else {
                                $dateDisplay = $date->format('M j, Y');
                            }
                            
                            // Format time
                            $time = new DateTime($apt['appointment_time']);
                            $timeDisplay = $time->format('g:i A');
                            
                            // Get property image
                            $image = !empty($apt['property_image']) 
                                ? htmlspecialchars($apt['property_image'])
                                : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400';
                        ?>
                        <div class="card appointment-card" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <!-- Image Section -->
                            <div class="appointment-image">
                                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($apt['property_title'] ?? 'Property'); ?>">
                                <span class="status-badge-overlay status-<?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span>
                            </div>

                            <!-- Main Content -->
                            <div class="appointment-details">
                                <div class="appointment-header">
                                    <div class="appointment-title">
                                        <h3><?php echo htmlspecialchars($apt['property_title'] ?? 'Untitled Property'); ?></h3>
                                    </div>
                                </div>

                                <div class="appointment-meta-row">
                                    <span class="meta-item"><i data-lucide="calendar"></i> <?php echo $dateDisplay; ?></span>
                                    <span class="meta-item"><i data-lucide="clock"></i> <?php echo $timeDisplay; ?></span>
                                    <span class="meta-item"><i data-lucide="map-pin"></i> <?php echo htmlspecialchars($apt['location'] ?? 'Location not available'); ?></span>
                                </div>

                                <div class="appointment-landlord-row">
                                    <img src="<?php echo $landlord['profile_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($landlordName); ?>" alt="Landlord" class="landlord-avatar-xs">
                                    <div class="landlord-info-text">
                                        <span class="landlord-name"><?php echo $landlordName; ?></span>
                                        <span class="landlord-role-badge">Landlord</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <?php if ($apt['status'] === 'pending'): ?>
                            <div class="appointment-actions">
                                <button class="icon-btn icon-btn-primary" title="Reschedule" onclick="openRescheduleModal(<?php echo $apt['appointment_id']; ?>)">
                                    <i data-lucide="calendar-clock" style="width: 1.25rem; height: 1.25rem;"></i>
                                </button>
                                <button class="icon-btn icon-btn-danger" title="Cancel" onclick="openCancelModal(<?php echo $apt['appointment_id']; ?>)">
                                    <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
                                </button>
                            </div>
                            <?php elseif ($apt['status'] === 'confirmed'): ?>
                            <div class="appointment-actions">
                                <button class="icon-btn icon-btn-danger" title="Cancel" onclick="openCancelModal(<?php echo $apt['appointment_id']; ?>)">
                                    <i data-lucide="x" style="width: 1.25rem; height: 1.25rem;"></i>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Past Appointments -->
                <?php if (!empty($pastAppointments)): ?>
                <div>
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin-bottom: 1.5rem;">Past</h2>
                    <div class="appointments-list">
                        <?php foreach ($pastAppointments as $index => $apt): 
                            $landlord = $userModel->getById($apt['landlord_id']);
                            $landlordName = htmlspecialchars($landlord['first_name'] . ' ' . $landlord['last_name']);
                            $date = new DateTime($apt['appointment_date']);
                            $dateDisplay = $date->format('M j, Y');
                            $time = new DateTime($apt['appointment_time']);
                            $timeDisplay = $time->format('g:i A');
                            $image = !empty($apt['property_image']) 
                                ? htmlspecialchars($apt['property_image'])
                                : 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400';
                        ?>
                        <div class="card appointment-card" style="opacity: 0.7; animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="appointment-image">
                                <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($apt['property_title'] ?? 'Property'); ?>">
                                <span class="status-badge-overlay status-<?php echo $apt['status']; ?>"><?php echo ucfirst($apt['status']); ?></span>
                            </div>
                            <div class="appointment-details">
                                <div class="appointment-header">
                                    <div class="appointment-title">
                                        <h3><?php echo htmlspecialchars($apt['property_title'] ?? 'Untitled Property'); ?></h3>
                                    </div>
                                </div>
                                <div class="appointment-meta-row">
                                    <span class="meta-item"><i data-lucide="calendar"></i> <?php echo $dateDisplay; ?></span>
                                    <span class="meta-item"><i data-lucide="clock"></i> <?php echo $timeDisplay; ?></span>
                                </div>
                                <div class="appointment-landlord-row">
                                    <img src="<?php echo $landlord['profile_photo'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($landlordName); ?>" alt="Landlord" class="landlord-avatar-xs">
                                    <div class="landlord-info-text">
                                        <span class="landlord-name"><?php echo $landlordName; ?></span>
                                        <span class="landlord-role-badge">Landlord</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Empty State -->
                <?php if (empty($upcomingAppointments) && empty($pastAppointments)): ?>
                <div class="card card-glass" style="padding: 3rem; text-align: center;">
                    <i data-lucide="calendar-x" style="width: 4rem; height: 4rem; color: rgba(0,0,0,0.3); margin: 0 auto 1rem;"></i>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin-bottom: 0.5rem;">No Appointments Yet</h3>
                    <p style="color: rgba(0,0,0,0.6);">Browse rooms and schedule viewings to get started!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Cancel Appointment</h3>
                <button class="close-modal" onclick="closeModal('cancelModal')"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this appointment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('cancelModal')">Keep Appointment</button>
                <button id="confirmCancelBtn" class="btn btn-danger">Yes, Cancel</button>
            </div>
        </div>
    </div>

    <!-- Reschedule Modal -->
    <div id="rescheduleModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Reschedule Appointment</h3>
                <button class="close-modal" onclick="closeModal('rescheduleModal')"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 1rem; color: #6b7280; font-size: 0.875rem;">Choose a new date and time for your viewing.</p>
                <div class="form-group">
                    <label class="form-label">New Date</label>
                    <input type="date" id="rescheduleDate" class="form-input" min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">New Time</label>
                    <input type="time" id="rescheduleTime" class="form-input">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('rescheduleModal')">Cancel</button>
                <button id="confirmRescheduleBtn" class="btn btn-primary">Confirm Reschedule</button>
            </div>
        </div>
    </div>
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    
    <script>
        let currentAppointmentId = null;

        function openCancelModal(id) {
            currentAppointmentId = id;
            document.getElementById('cancelModal').classList.add('show');
        }

        function openRescheduleModal(id) {
            currentAppointmentId = id;
            document.getElementById('rescheduleModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            currentAppointmentId = null;
        }

        // Close modals on outside click
        window.onclick = function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.classList.remove('show');
                currentAppointmentId = null;
            }
        }

        // Handle Cancel
        document.getElementById('confirmCancelBtn').addEventListener('click', async function() {
            if (!currentAppointmentId) return;

            this.disabled = true;
            this.innerHTML = 'Cancelling...';

            try {
                const formData = new FormData();
                formData.append('appointment_id', currentAppointmentId);
                formData.append('status', 'cancelled');

                const response = await fetch('/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/controllers/AppointmentController.php?action=update_status', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to cancel appointment');
                    this.disabled = false;
                    this.innerHTML = 'Yes, Cancel';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
                this.disabled = false;
                this.innerHTML = 'Yes, Cancel';
            }
        });

        // Handle Reschedule
        document.getElementById('confirmRescheduleBtn').addEventListener('click', async function() {
            if (!currentAppointmentId) return;

            const date = document.getElementById('rescheduleDate').value;
            const time = document.getElementById('rescheduleTime').value;

            if (!date || !time) {
                alert('Please select both date and time');
                return;
            }

            this.disabled = true;
            this.innerHTML = 'Rescheduling...';

            try {
                const formData = new FormData();
                formData.append('appointment_id', currentAppointmentId);
                formData.append('date', date);
                formData.append('time', time);

                const response = await fetch('/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/controllers/AppointmentController.php?action=reschedule', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to reschedule appointment');
                    this.disabled = false;
                    this.innerHTML = 'Confirm Reschedule';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
                this.disabled = false;
                this.innerHTML = 'Confirm Reschedule';
            }
        });
    </script>
</body>
</html>
