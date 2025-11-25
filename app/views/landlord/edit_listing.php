<?php
// Start session and load models
session_start();
require_once __DIR__ . '/../../models/Listing.php';

// Check if user is logged in as landlord
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
    header('Location: /Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/public/login.php');
    exit;
}

$listingId = $_GET['id'] ?? 0;
$listingModel = new Listing();
$listing = $listingModel->getWithImages($listingId);

if (!$listing || $listing['landlord_id'] !== $_SESSION['user_id']) {
    echo "Listing not found or access denied.";
    exit;
}

// Pre-process data for form
$amenities = $listing['amenities'] ?? [];
$houseRules = $listing['house_rules_data'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Listing - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/landlord.module.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>
<body>
    <div class="landlord-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="landlord-container">
            <!-- Header -->
            <div class="page-header animate-slide-up">
                <div>
                    <h1 class="page-title">Edit Listing</h1>
                    <p class="page-subtitle">Update your property details. Note: Changes will require re-approval.</p>
                </div>
            </div>

            <form id="editListingForm" action="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/controllers/ListingController.php?action=update" method="POST" enctype="multipart/form-data" class="animate-slide-up add-listing-grid" style="animation-delay: 0.1s;">
                <input type="hidden" name="listing_id" value="<?php echo $listingId; ?>">
                <input type="hidden" name="room_type" value="private_room">
                
                <div id="formStatus" style="grid-column: 1 / -1; display: none; padding: 0.75rem 1rem; border-radius: 0.75rem;"></div>
                
                <!-- Image Upload -->
                <div class="glass-card add-listing-card add-listing-card-full">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Property Images</h2>
                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.6); margin-bottom: 1rem;">Existing images (click X to remove):</p>
                    <div id="existingImagesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                        <?php foreach ($listing['images'] as $img): ?>
                        <div class="relative group" style="position: relative;">
                            <img src="<?php echo $img['image_url']; ?>" style="width: 100%; height: 8rem; object-fit: cover; border-radius: 0.75rem;">
                            <input type="hidden" name="existing_images[]" value="<?php echo $img['image_id']; ?>">
                            <button type="button" class="remove-existing-btn" onclick="this.parentElement.remove()" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem; background-color: #ef4444; color: white; border-radius: 9999px; border: none; cursor: pointer;">
                                <i data-lucide="x" style="width: 1rem; height: 1rem;"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <p style="font-size: 0.875rem; color: rgba(0,0,0,0.6); margin-bottom: 1rem;">Add new images:</p>
                    <div id="imageGrid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <!-- Images will be added here dynamically -->
                        <label class="image-upload-label" style="width: 100%; height: 8rem; border: 2px dashed rgba(0,0,0,0.2); border-radius: 0.75rem; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                            <i data-lucide="upload" style="width: 2rem; height: 2rem; color: rgba(0,0,0,0.4); margin-bottom: 0.5rem;"></i>
                            <span style="font-size: 0.875rem; color: rgba(0,0,0,0.6);">Upload Image</span>
                            <input type="file" accept="image/*" multiple id="imageInput" name="images[]" style="display: none;">
                        </label>
                    </div>
                </div>

                <div class="add-listing-columns">
                    <div class="add-listing-column">
                        <!-- Basic Information -->
                        <div class="glass-card add-listing-card">
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Basic Information</h2>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <label class="form-label">Property Title</label>
                                    <div class="input-wrapper">
                                        <i data-lucide="home" class="input-icon-blue"></i>
                                        <input type="text" name="title" class="form-input with-icon" placeholder="e.g., Modern Studio in Downtown" required value="<?php echo htmlspecialchars($listing['title']); ?>">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-input" placeholder="Describe your property..." style="min-height: 120px; resize: vertical;" required><?php echo htmlspecialchars($listing['description']); ?></textarea>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label class="form-label">Monthly Rent</label>
                                        <div class="input-wrapper">
                                            <i data-lucide="coins" class="input-icon-blue"></i>
                                            <input type="number" name="price" class="form-input with-icon" placeholder="1200" min="0" step="50" required value="<?php echo $listing['price']; ?>">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label">Security Deposit</label>
                                        <div class="input-wrapper">
                                            <i data-lucide="shield" class="input-icon-blue"></i>
                                            <input type="number" name="security_deposit" class="form-input with-icon" placeholder="1200" min="0" step="50" value="<?php echo $listing['security_deposit']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div>
                                        <label class="form-label">Available From</label>
                                        <input type="date" name="available_from" class="form-input" required value="<?php echo $listing['available_from']; ?>">
                                    </div>
                                    <div style="display: flex; align-items: flex-end;">
                                        <label class="glass-subtle" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.75rem; cursor: pointer; transition: all 0.2s; width: 100%; height: 3rem;">
                                            <input type="checkbox" name="utilities_included" value="1" style="width: 1rem; height: 1rem; border-radius: 0.25rem; accent-color: var(--deep-blue);" <?php echo $listing['utilities_included'] ? 'checked' : ''; ?>>
                                            <span style="font-size: 0.875rem; font-weight: 500; color: #000;">Utilities Included</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label">Full Address</label>
                                    <div class="input-wrapper">
                                    <i data-lucide="map-pin" class="input-icon-blue"></i>
                                    <input type="text" name="location" class="form-input with-icon" placeholder="e.g., 123 Market St, San Francisco, CA" required value="<?php echo htmlspecialchars($listing['location']); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div class="glass-card add-listing-card">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Amenities</h2>
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="wifi" <?php echo (in_array('wifi', $amenities) || in_array('WiFi', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="wifi"></i>
                                    <span>WiFi</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="parking" <?php echo (in_array('parking', $amenities) || in_array('Parking', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="car"></i>
                                    <span>Parking</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="kitchen" <?php echo (in_array('kitchen', $amenities) || in_array('Kitchen', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="coffee"></i>
                                    <span>Kitchen</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="gym" <?php echo (in_array('gym', $amenities) || in_array('Gym', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="dumbbell"></i>
                                    <span>Gym</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="air_conditioning" <?php echo (in_array('air_conditioning', $amenities) || in_array('Air Conditioning', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="wind"></i>
                                    <span>Air Conditioning</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="heating" <?php echo (in_array('heating', $amenities) || in_array('Heating', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="flame"></i>
                                    <span>Heating</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="washer_dryer" <?php echo (in_array('washer_dryer', $amenities) || in_array('Washer', $amenities) || in_array('Dryer', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="shirt"></i>
                                    <span>Washer/Dryer</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="dishwasher" <?php echo (in_array('dishwasher', $amenities) || in_array('Dishwasher', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="utensils"></i>
                                    <span>Dishwasher</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="elevator" <?php echo (in_array('elevator', $amenities) || in_array('Elevator', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="arrow-up-circle"></i>
                                    <span>Elevator</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="balcony" <?php echo (in_array('balcony', $amenities) || in_array('Balcony', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="sun"></i>
                                    <span>Balcony/Patio</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="pool" <?php echo (in_array('pool', $amenities) || in_array('Pool', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="waves"></i>
                                    <span>Pool</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="security" <?php echo (in_array('security', $amenities) || in_array('Security System', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="shield-check"></i>
                                    <span>Security System</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="tv" <?php echo (in_array('tv', $amenities) || in_array('TV', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="tv"></i>
                                    <span>TV</span>
                                </label>
                                <label class="glass-subtle amenity-option">
                                    <input type="checkbox" name="amenities[]" value="essentials" <?php echo (in_array('essentials', $amenities) || in_array('Essentials', $amenities)) ? 'checked' : ''; ?>>
                                    <i data-lucide="package"></i>
                                    <span>Essentials</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="add-listing-column">
                        <!-- Property Details -->
                        <div class="glass-card add-listing-card">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Property Details</h2>
                            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                                <div>
                                    <label class="form-label">Bedrooms</label>
                                    <select name="bedrooms" class="form-input">
                                        <option value="1" <?php echo $listing['bedrooms'] == 1 ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo $listing['bedrooms'] == 2 ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo $listing['bedrooms'] == 3 ? 'selected' : ''; ?>>3</option>
                                        <option value="4" <?php echo $listing['bedrooms'] >= 4 ? 'selected' : ''; ?>>4+</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Bathrooms</label>
                                    <select name="bathrooms" class="form-input">
                                        <option value="1" <?php echo $listing['bathrooms'] == 1 ? 'selected' : ''; ?>>1</option>
                                        <option value="1.5" <?php echo $listing['bathrooms'] == 1.5 ? 'selected' : ''; ?>>1.5</option>
                                        <option value="2" <?php echo $listing['bathrooms'] == 2 ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo $listing['bathrooms'] >= 3 ? 'selected' : ''; ?>>3+</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Current Roommates</label>
                                    <select name="current_roommates" class="form-input">
                                        <option value="0" <?php echo $listing['current_roommates'] == 0 ? 'selected' : ''; ?>>0</option>
                                        <option value="1" <?php echo $listing['current_roommates'] == 1 ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo $listing['current_roommates'] == 2 ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo $listing['current_roommates'] >= 3 ? 'selected' : ''; ?>>3+</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- House Rules -->
                        <div class="glass-card add-listing-card">
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: #000; margin-bottom: 1rem;">House Rules</h2>
                            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="cigarette" class="rule-icon"></i>
                                        <span style="font-weight: 500;">Smoking Allowed</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[smoking_allowed]" value="1" <?php echo !empty($houseRules['smoking_allowed']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <i data-lucide="paw-print" class="rule-icon"></i>
                                            <span style="font-weight: 500;">Pets Allowed</span>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" name="house_rules[pets_allowed]" value="1" id="petsAllowedCheckbox" onchange="togglePetsDetails()" <?php echo !empty($houseRules['pets_allowed']) ? 'checked' : ''; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div id="petsDetails" style="display: <?php echo !empty($houseRules['pets_allowed']) ? 'block' : 'none'; ?>; padding-left: 0.75rem;">
                                        <input type="text" name="house_rules[pets_details]" class="form-input" placeholder="e.g., Cats only, Small dogs under 20lbs" style="font-size: 0.875rem;" value="<?php echo htmlspecialchars($houseRules['pets_details'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="music" class="rule-icon"></i>
                                        <span style="font-weight: 500;">No Parties/Events</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[no_parties]" value="1" <?php echo !empty($houseRules['no_parties']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="users" class="rule-icon"></i>
                                        <span style="font-weight: 500;">No Overnight Guests</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[no_guests]" value="1" <?php echo !empty($houseRules['no_guests']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="trash-2" class="rule-icon"></i>
                                        <span style="font-weight: 500;">Clean Up After Yourself</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[clean_up]" value="1" <?php echo !empty($houseRules['clean_up']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="footprints" class="rule-icon"></i>
                                        <span style="font-weight: 500;">No Shoes Inside</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[no_shoes]" value="1" <?php echo !empty($houseRules['no_shoes']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="recycle" class="rule-icon"></i>
                                        <span style="font-weight: 500;">Recycling Required</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[recycling_required]" value="1" <?php echo !empty($houseRules['recycling_required']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="sparkles" class="rule-icon"></i>
                                        <span style="font-weight: 500;">Keep Common Areas Clean</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[keep_common_areas_clean]" value="1" <?php echo !empty($houseRules['keep_common_areas_clean']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; background: rgba(255,255,255,0.4); border-radius: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i data-lucide="lightbulb" class="rule-icon"></i>
                                        <span style="font-weight: 500;">Turn Off Lights When Leaving</span>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="house_rules[turn_off_lights]" value="1" <?php echo !empty($houseRules['turn_off_lights']) ? 'checked' : ''; ?>>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div>
                                    <label class="form-label">Quiet Hours</label>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <input type="time" name="house_rules[quiet_hours_start]" class="form-input" value="<?php echo $houseRules['quiet_hours_start'] ?? ''; ?>">
                                        <span style="color: rgba(0,0,0,0.4);">to</span>
                                        <input type="time" name="house_rules[quiet_hours_end]" class="form-input" value="<?php echo $houseRules['quiet_hours_end'] ?? ''; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="add-listing-actions">
                    <a href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/landlord/listings.php" class="btn btn-ghost btn-lg" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Cancel</a>
                    <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <script>
        lucide.createIcons();

        // Image Upload Logic
        const imageInput = document.getElementById('imageInput');
        const imageGrid = document.getElementById('imageGrid');
        const uploadLabel = document.querySelector('.image-upload-label');
        let allFiles = [];

        imageInput.addEventListener('change', (e) => {
            const newFiles = Array.from(e.target.files);
            allFiles = allFiles.concat(newFiles);
            updateImageInput();
            renderPreviews();
        });

        function updateImageInput() {
            const dt = new DataTransfer();
            allFiles.forEach(file => dt.items.add(file));
            imageInput.files = dt.files;
        }

        function renderPreviews() {
            const previews = imageGrid.querySelectorAll('.relative.group');
            previews.forEach(p => p.remove());

            allFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    createImageElement(e.target.result, index);
                };
                reader.readAsDataURL(file);
            });
        }

        function createImageElement(url, index) {
            const div = document.createElement('div');
            div.className = 'relative group';
            div.style.position = 'relative';
            
            div.innerHTML = `
                <img src="${url}" style="width: 100%; height: 8rem; object-fit: cover; border-radius: 0.75rem;">
                <button type="button" class="remove-btn" data-index="${index}" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.25rem; background-color: #ef4444; color: white; border-radius: 9999px; border: none; cursor: pointer; opacity: 0; transition: opacity 0.2s;">
                    <i data-lucide="x" style="width: 1rem; height: 1rem;"></i>
                </button>
            `;

            div.addEventListener('mouseenter', () => {
                div.querySelector('.remove-btn').style.opacity = '1';
            });
            div.addEventListener('mouseleave', () => {
                div.querySelector('.remove-btn').style.opacity = '0';
            });

            div.querySelector('.remove-btn').addEventListener('click', (e) => {
                const idx = parseInt(e.currentTarget.dataset.index);
                allFiles.splice(idx, 1);
                updateImageInput();
                renderPreviews();
            });

            imageGrid.insertBefore(div, uploadLabel);
            lucide.createIcons();
        }

        const editListingForm = document.getElementById('editListingForm');

        editListingForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const loadingToast = Toastify({
                text: "Saving changes...",
                duration: -1, // Keep open until manually closed
                close: false,
                gravity: "top",
                position: "right",
                style: { background: "var(--deep-blue)" }
            }).showToast();

            const formData = new FormData(editListingForm);

            try {
                const response = await fetch(editListingForm.action, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();
                
                // Hide loading toast
                loadingToast.hideToast();

                if (data.success) {
                    Toastify({
                        text: data.message,
                        duration: 5000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        style: { background: "#10B981" }
                    }).showToast();

                    setTimeout(() => {
                        window.location.href = '/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/app/views/landlord/listings.php';
                    }, 2000);
                    
                } else {
                    Toastify({
                        text: data.message || "Failed to update listing",
                        duration: 4000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        style: { background: "#EF4444" }
                    }).showToast();
                }

            } catch (error) {
                console.error(error);
                loadingToast.hideToast(); // Ensure loading toast is hidden on error
                Toastify({
                    text: "Something went wrong while updating the listing.",
                    duration: 4000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: { background: "#EF4444" }
                }).showToast();
            }
        });

        function togglePetsDetails() {
            const checkbox = document.getElementById('petsAllowedCheckbox');
            const details = document.getElementById('petsDetails');
            if (checkbox.checked) {
                details.style.display = 'block';
                details.classList.add('animate-slide-up');
            } else {
                details.style.display = 'none';
            }
        }
    </script>
</body>
</html>
