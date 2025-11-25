<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Listing - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/cards.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/forms.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/room-card.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/room-details.module.css">
</head>
<body>
    <div style="min-height: 100vh; background: linear-gradient(to bottom right, var(--softBlue-20), var(--neutral), var(--deepBlue-10));">
        <?php 
        session_start();
        require_once __DIR__ . '/../../models/Listing.php';
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
            header('Location: /Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/public/login.php');
            exit;
        }

        $listingId = $_GET['id'] ?? 0;
        $listingModel = new Listing();
        $listing = $listingModel->getWithImages($listingId);

        if (!$listing || $listing['landlord_id'] !== $_SESSION['user_id']) {
            echo "<div style='padding: 2rem; text-align: center;'>Listing not found or access denied.</div>";
            exit;
        }

        include __DIR__ . '/../includes/navbar.php'; 
        ?>
        <div style="padding-top: 6rem; padding-bottom: 5rem; padding-left: 1rem; padding-right: 1rem;">
            <div style="max-width: 1280px; margin: 0 auto;">
                <!-- Back Button -->
                <a href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/landlord/listings.php" class="btn btn-ghost btn-sm" style="margin-bottom: 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <i data-lucide="arrow-left" class="btn-icon"></i>
                    Back to My Listings
                </a>

                <!-- Status Banner -->
                <?php if ($listing['approval_status'] === 'pending'): ?>
                    <div style="background: #fffbeb; border: 1px solid #fcd34d; color: #92400e; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i data-lucide="clock" style="width: 1.5rem; height: 1.5rem;"></i>
                        <div>
                            <strong>Pending Approval</strong>
                            <p style="margin: 0; font-size: 0.875rem;">This listing is waiting for admin approval before it becomes visible to seekers.</p>
                        </div>
                    </div>
                <?php elseif ($listing['approval_status'] === 'rejected'): ?>
                    <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #b91c1c; padding: 1rem; border-radius: 0.75rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                        <i data-lucide="alert-circle" style="width: 1.5rem; height: 1.5rem;"></i>
                        <div>
                            <strong>Rejected</strong>
                            <p style="margin: 0; font-size: 0.875rem;">Admin Note: <?php echo htmlspecialchars($listing['admin_note'] ?? 'No reason provided.'); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Image Gallery -->
                <div style="margin-bottom: 3rem; animation: slideUp 0.3s ease-out;">
                    <div class="room-gallery-main">
                        <?php 
                        $primaryImage = !empty($listing['images']) ? $listing['images'][0]['image_url'] : 'https://via.placeholder.com/1200x800?text=No+Image';
                        ?>
                        <img src="<?php echo $primaryImage; ?>" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                    </div>
                    <?php if (!empty($listing['images']) && count($listing['images']) > 1): ?>
                    <div class="room-gallery-thumbnails">
                        <?php foreach ($listing['images'] as $index => $img): ?>
                        <div class="room-thumbnail <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo $img['image_url']; ?>" alt="Thumbnail <?php echo $index + 1; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr; gap: 2rem;">
                    <!-- Main Content -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <!-- Title & Location -->
                        <div>
                            <h1 style="font-size: 1.875rem; font-weight: 700; color: #000000; margin: 0 0 0.75rem 0;"><?php echo htmlspecialchars($listing['title']); ?></h1>
                            <div style="display: flex; align-items: center; gap: 0.5rem; color: rgba(0,0,0,0.6); margin-bottom: 1rem;">
                                <i data-lucide="map-pin" style="width: 1.25rem; height: 1.25rem;"></i>
                                <span style="font-size: 1.125rem;"><?php echo htmlspecialchars($listing['location']); ?></span>
                            </div>
                        </div>

                        <!-- Quick Stats -->
                        <div class="card card-glass" style="padding: 1.5rem;">
                            <div class="room-stats-grid">
                                <div class="room-stat-item">
                                    <div class="room-stat-icon"><i data-lucide="bed"></i></div>
                                    <p class="room-stat-label">Bedrooms</p>
                                    <p class="room-stat-value"><?php echo $listing['bedrooms'] ?? 0; ?></p>
                                </div>
                                <div class="room-stat-item">
                                    <div class="room-stat-icon"><i data-lucide="bath"></i></div>
                                    <p class="room-stat-label">Bathrooms</p>
                                    <p class="room-stat-value"><?php echo $listing['bathrooms'] ?? 0; ?></p>
                                </div>
                                <div class="room-stat-item">
                                    <div class="room-stat-icon"><i data-lucide="users"></i></div>
                                    <p class="room-stat-label">Roommates</p>
                                    <p class="room-stat-value"><?php echo $listing['current_roommates'] ?? 0; ?></p>
                                </div>
                                <div class="room-stat-item">
                                    <div class="room-stat-icon"><i data-lucide="calendar"></i></div>
                                    <p class="room-stat-label">Available</p>
                                    <p class="room-stat-value"><?php echo $listing['available_from'] ? date('M d', strtotime($listing['available_from'])) : 'Now'; ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="card card-glass" style="padding: 1.5rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin: 0 0 1rem 0;">About this room</h2>
                            <div style="display: flex; flex-direction: column; gap: 1rem; color: rgba(0,0,0,0.7); line-height: 1.6;">
                                <p><?php echo nl2br(htmlspecialchars($listing['description'])); ?></p>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <?php if (!empty($listing['amenities'])): ?>
                        <div class="card card-glass" style="padding: 1.5rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin: 0 0 1rem 0;">Amenities & Features</h2>
                            <div class="amenities-grid">
                                <?php 
                                $amenityMap = [
                                    'wifi' => ['label' => 'WiFi', 'icon' => 'wifi'],
                                    'parking' => ['label' => 'Parking', 'icon' => 'car'],
                                    'kitchen' => ['label' => 'Kitchen', 'icon' => 'coffee'],
                                    'gym' => ['label' => 'Gym', 'icon' => 'dumbbell'],
                                    'air_conditioning' => ['label' => 'Air Conditioning', 'icon' => 'wind'],
                                    'heating' => ['label' => 'Heating', 'icon' => 'flame'],
                                    'washer_dryer' => ['label' => 'Washer/Dryer', 'icon' => 'shirt'],
                                    'dishwasher' => ['label' => 'Dishwasher', 'icon' => 'utensils'],
                                    'elevator' => ['label' => 'Elevator', 'icon' => 'arrow-up-circle'],
                                    'balcony' => ['label' => 'Balcony/Patio', 'icon' => 'sun'],
                                    'pool' => ['label' => 'Pool', 'icon' => 'waves'],
                                    'security' => ['label' => 'Security System', 'icon' => 'shield-check'],
                                    'tv' => ['label' => 'TV', 'icon' => 'tv'],
                                    'essentials' => ['label' => 'Essentials', 'icon' => 'package']
                                ];

                                foreach ($listing['amenities'] as $amenityValue): 
                                    // Fallback if the value isn't in our map
                                    $item = $amenityMap[$amenityValue] ?? ['label' => ucwords(str_replace('_', ' ', $amenityValue)), 'icon' => 'check'];
                                ?>
                                <div class="amenity-item">
                                    <div class="amenity-icon"><i data-lucide="<?php echo $item['icon']; ?>"></i></div>
                                    <span class="amenity-label"><?php echo htmlspecialchars($item['label']); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- House Rules -->
                        <?php if (!empty($listing['house_rules_data'])): ?>
                        <div class="card card-glass" style="padding: 1.5rem;">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000000; margin: 0 0 1rem 0;">House Rules</h2>
                            <div class="house-rules-list">
                                <?php 
                                $houseRuleIcons = [
                                    'smoking_allowed' => 'cigarette',
                                    'pets_allowed' => 'paw-print',
                                    'no_parties' => 'music',
                                    'no_guests' => 'users',
                                    'clean_up' => 'trash-2',
                                    'no_shoes' => 'footprints',
                                    'recycling_required' => 'recycle',
                                    'keep_common_areas_clean' => 'sparkles',
                                    'turn_off_lights' => 'lightbulb'
                                ];
                                
                                foreach ($listing['house_rules_data'] as $key => $value): 
                                    if ($key === 'pets_details' || $key === 'quiet_hours_start' || $key === 'quiet_hours_end') continue;
                                    // Skip if value is false/0 or string "0"
                                    if (!$value || $value === '0') continue;

                                    $label = ucwords(str_replace('_', ' ', $key));
                                    $icon = $houseRuleIcons[$key] ?? 'check-circle';
                                ?>
                                <div class="house-rule-item">
                                    <div class="house-rule-icon allowed">
                                        <i data-lucide="<?php echo $icon; ?>"></i>
                                    </div>
                                    <div class="house-rule-content">
                                        <span class="house-rule-text"><?php echo $label; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (!empty($listing['house_rules_data']['pets_details'])): ?>
                                <div class="house-rule-item">
                                    <div class="house-rule-icon allowed"><i data-lucide="paw-print"></i></div>
                                    <div class="house-rule-content">
                                        <span class="house-rule-text">Pets: <?php echo htmlspecialchars($listing['house_rules_data']['pets_details']); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($listing['house_rules_data']['quiet_hours_start']) && !empty($listing['house_rules_data']['quiet_hours_end'])): ?>
                                <div class="house-rule-item">
                                    <div class="house-rule-icon allowed"><i data-lucide="moon"></i></div>
                                    <div class="house-rule-content">
                                        <span class="house-rule-text">Quiet Hours: <?php echo date('g:i A', strtotime($listing['house_rules_data']['quiet_hours_start'])); ?> - <?php echo date('g:i A', strtotime($listing['house_rules_data']['quiet_hours_end'])); ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar -->
                    <div>
                        <div class="card card-glass-strong booking-card" style="padding: 1.5rem;">
                            <div style="margin-bottom: 1.5rem;">
                                <div class="booking-price">
                                    <span class="booking-price-amount">₱<?php echo number_format($listing['price']); ?></span>
                                    <span class="booking-price-period">/month</span>
                                </div>
                                <p class="booking-price-note">
                                    <?php echo $listing['utilities_included'] ? 'Utilities included' : 'Utilities not included'; ?> 
                                    • Security deposit: ₱<?php echo number_format($listing['security_deposit']); ?>
                                </p>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                                <a href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/landlord/edit_listing.php?id=<?php echo $listingId; ?>" class="btn btn-primary btn-lg" style="width: 100%; text-decoration: none; display: flex; justify-content: center; align-items: center; gap: 0.5rem;">
                                    <i data-lucide="edit" class="btn-icon"></i>
                                    Edit Listing
                                </a>
                                <button id="deleteListingBtn" class="btn btn-glass btn-lg" style="width: 100%; color: #dc2626; border-color: #fca5a5;">
                                    <i data-lucide="trash-2" class="btn-icon"></i>
                                    Delete Listing
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                    @media (min-width: 1024px) {
                        div[style*="grid-template-columns: 1fr"][style*="gap: 2rem"] {
                            grid-template-columns: 2fr 1fr !important;
                        }
                    }

                    /* Modal Styles */
                    .modal-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        backdrop-filter: blur(4px);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        z-index: 1000;
                        opacity: 0;
                        transition: opacity 0.2s ease-out;
                    }
                    .modal-overlay.show {
                        display: flex;
                        opacity: 1;
                    }
                    .modal-content {
                        width: 90%;
                        max-width: 450px;
                        padding: 1.5rem;
                        border-radius: 1rem;
                        transform: translateY(20px);
                        transition: transform 0.3s ease-out;
                        background: rgba(255, 255, 255, 0.95);
                        border: 1px solid rgba(255, 255, 255, 0.5);
                        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                    }
                    .modal-overlay.show .modal-content {
                        transform: translateY(0);
                    }
                    .modal-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 1rem;
                    }
                    .modal-title {
                        font-size: 1.25rem;
                        font-weight: 700;
                        color: #1f2937;
                    }
                    .modal-body {
                        color: #4b5563;
                        margin-bottom: 1.5rem;
                        line-height: 1.5;
                    }
                    .modal-footer {
                        display: flex;
                        justify-content: flex-end;
                        gap: 0.75rem;
                    }
                    .modal-footer button {
                        padding: 0.5rem 1rem;
                        height: auto;
                        min-height: 2.5rem;
                        white-space: nowrap;
                    }
                </style>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Delete Listing</h3>
                <button class="close-modal" style="color: #6b7280; padding: 0.25rem;"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this listing? This action cannot be undone and will remove all associated data and images.</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost close-modal">Cancel</button>
                <button id="confirmDeleteBtn" class="btn btn-primary" style="background: #dc2626; border-color: #dc2626; color: white;">Delete Listing</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        // Image Gallery Logic
        document.addEventListener('DOMContentLoaded', () => {
            const mainImage = document.querySelector('.room-gallery-main img');
            const thumbnails = document.querySelectorAll('.room-thumbnail');

            thumbnails.forEach(thumb => {
                thumb.addEventListener('click', function() {
                    // Update main image src
                    const newSrc = this.querySelector('img').src;
                    mainImage.src = newSrc;

                    // Update active state
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Delete Listing Logic
            const deleteBtn = document.getElementById('deleteListingBtn');
            const deleteModal = document.getElementById('deleteModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            const closeModalBtns = document.querySelectorAll('.close-modal');

            if (deleteBtn && deleteModal) {
                // Open Modal
                deleteBtn.addEventListener('click', () => {
                    deleteModal.classList.add('show');
                });

                // Close Modal
                closeModalBtns.forEach(btn => {
                    btn.addEventListener('click', () => {
                        deleteModal.classList.remove('show');
                    });
                });

                // Close on click outside
                deleteModal.addEventListener('click', (e) => {
                    if (e.target === deleteModal) {
                        deleteModal.classList.remove('show');
                    }
                });

                // Confirm Delete
                confirmDeleteBtn.addEventListener('click', async function() {
                    // Disable button to prevent double clicks
                    this.disabled = true;
                    this.innerHTML = '<i data-lucide="loader-2" class="animate-spin"></i> Deleting...';
                    lucide.createIcons();

                    try {
                        const formData = new FormData();
                        formData.append('listing_id', <?php echo $listingId; ?>);

                        const response = await fetch('/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/controllers/ListingController.php?action=delete', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            window.location.href = '/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/landlord/listings.php';
                        } else {
                            alert(result.message || 'Failed to delete listing.');
                            this.disabled = false;
                            this.innerHTML = 'Delete Listing';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the listing.');
                        this.disabled = false;
                        this.innerHTML = 'Delete Listing';
                    }
                });
            }
        });
    </script>
</body>
</html>
