<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/profile-settings.module.css">
</head>
<body>
    <div class="profile-page">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>

        <div class="profile-container">
            <!-- Header -->
            <div class="profile-header">
                <h1 class="page-title">Profile Settings</h1>
                <p class="page-subtitle">Manage your account settings and preferences</p>
            </div>

            <!-- 2 Column Layout -->
            <div class="profile-grid">
                <!-- Left Column -->
                <div class="column">
                    <!-- 1. Profile Section -->
                    <div class="profile-card">
                        <div class="profile-header-content">
                            <div class="profile-image-wrapper">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Profile" class="profile-image">
                                <button class="camera-button">
                                    <i data-lucide="camera" style="width: 14px; height: 14px;"></i>
                                </button>
                            </div>
                            <div style="flex: 1;">
                                <h2 class="profile-name">John Doe</h2>
                                <p class="profile-occupation">Software Engineer</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. About Me Section -->
                    <div class="profile-card">
                        <h3 class="card-title">About Me</h3>
                        <textarea class="form-textarea" rows="3" placeholder="Tell potential roommates about yourself...">Looking for a quiet, clean space near downtown. Non-smoker, no pets.</textarea>
                    </div>

                    <!-- 3. Personal Information -->
                    <div class="profile-card">
                        <h3 class="card-title">Personal Information</h3>
                        <div class="form-group-stack">
                            <div>
                                <label class="form-label">Full Name</label>
                                <div class="input-wrapper">
                                    <i data-lucide="user" class="input-icon"></i>
                                    <input type="text" class="form-input" value="John Doe">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Email Address</label>
                                <div class="input-wrapper">
                                    <i data-lucide="mail" class="input-icon"></i>
                                    <input type="email" class="form-input" value="john.doe@email.com">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Phone Number</label>
                                <div class="input-wrapper">
                                    <i data-lucide="phone" class="input-icon"></i>
                                    <input type="tel" class="form-input" value="+1 (555) 123-4567">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Location</label>
                                <div class="input-wrapper">
                                    <i data-lucide="map-pin" class="input-icon"></i>
                                    <input type="text" class="form-input" value="San Francisco, CA">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Occupation</label>
                                <div class="input-wrapper">
                                    <i data-lucide="briefcase" class="input-icon"></i>
                                    <input type="text" class="form-input" value="Software Engineer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6. Room Details Section -->
                    <div class="profile-card">
                        <h3 class="card-title">Room Details</h3>
                        <div class="form-group-stack">
                            <div>
                                <label class="form-label">Monthly Budget</label>
                                <div class="input-wrapper">
                                    <i data-lucide="dollar-sign" class="input-icon"></i>
                                    <input type="number" class="form-input" value="1200">
                                </div>
                            </div>

                            <div>
                                <label class="form-label">Move-in Date</label>
                                <div class="input-wrapper">
                                    <i data-lucide="calendar" class="input-icon"></i>
                                    <input type="date" class="form-input" value="2024-02-01">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="column">
                    <!-- 4. Lifestyle Section -->
                    <div class="profile-card">
                        <h3 class="card-title">Lifestyle</h3>
                        <div class="form-group-stack">
                            <div>
                                <label class="form-label">Sleep Schedule</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="early">Early Bird</option>
                                    <option value="night">Night Owl</option>
                                    <option value="flexible">Flexible</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Social Level</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="outgoing">Social/Outgoing</option>
                                    <option value="balanced">Balanced</option>
                                    <option value="quiet">Quiet/Private</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Guests</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="rarely">Rarely</option>
                                    <option value="occasionally">Occasionally</option>
                                    <option value="frequently">Frequently</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Cleanliness</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="very">Very Clean</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="relaxed">Relaxed</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Work Schedule</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="office">Office (9-5)</option>
                                    <option value="hybrid">Hybrid</option>
                                    <option value="remote">Remote</option>
                                    <option value="shift">Shift Work</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Noise Level</label>
                                <select class="form-select">
                                    <option value="">Select...</option>
                                    <option value="quiet">Quiet</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="lively">Lively</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Preferences Section -->
                    <div class="profile-card">
                        <div class="preferences-header">
                            <h3 class="card-title" style="margin-bottom: 0;">My Preferences</h3>
                            <span class="preferences-count" id="preference-counter">0/6 selected</span>
                        </div>
                        <p class="preferences-subtitle">Select up to 6 preferences that matter most to you</p>

                        <div class="preferences-list">
                            <?php
                            $preferences = [
                                ['key' => 'veryClean', 'label' => 'Very Clean', 'icon' => 'sparkles'],
                                ['key' => 'quietEnvironment', 'label' => 'Quiet Environment', 'icon' => 'volume-2'],
                                ['key' => 'nonSmoker', 'label' => 'Non-smoker', 'icon' => 'cigarette'],
                                ['key' => 'petFriendly', 'label' => 'Pet-friendly', 'icon' => 'paw-print'],
                                ['key' => 'socialOutgoing', 'label' => 'Social/Outgoing', 'icon' => 'users'],
                                ['key' => 'earlyBird', 'label' => 'Early Bird', 'icon' => 'sun'],
                                ['key' => 'nightOwl', 'label' => 'Night Owl', 'icon' => 'moon'],
                                ['key' => 'enjoysCooking', 'label' => 'Enjoys Cooking', 'icon' => 'utensils'],
                                ['key' => 'fitnessEnthusiast', 'label' => 'Fitness Enthusiast', 'icon' => 'target'],
                                ['key' => 'studentFriendly', 'label' => 'Student-friendly', 'icon' => 'graduation-cap'],
                                ['key' => 'workingProfessional', 'label' => 'Working Professional', 'icon' => 'briefcase'],
                                ['key' => 'veganVegetarian', 'label' => 'Vegan/Vegetarian', 'icon' => 'leaf'],
                                ['key' => 'organized', 'label' => 'Organized', 'icon' => 'target'],
                                ['key' => 'relaxedEasygoing', 'label' => 'Relaxed/Easygoing', 'icon' => 'smile']
                            ];
                            
                            foreach ($preferences as $pref): ?>
                            <label class="preference-option" data-key="<?php echo $pref['key']; ?>">
                                <input type="checkbox" class="preference-checkbox" style="display: none;"> <!-- Hidden checkbox for logic -->
                                <div style="display: flex; align-items: center; gap: 0.375rem;">
                                    <!-- Custom Checkbox Visual -->
                                    <div class="custom-checkbox" style="width: 14px; height: 14px; border: 1px solid #d1d5db; border-radius: 4px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;">
                                        <i data-lucide="check" style="width: 10px; height: 10px; color: white; opacity: 0;"></i>
                                    </div>
                                    <i data-lucide="<?php echo $pref['icon']; ?>" class="preference-icon"></i>
                                    <span class="preference-label"><?php echo $pref['label']; ?></span>
                                </div>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer - Save Button -->
            <div class="profile-footer">
                <button class="btn-primary">
                    <i data-lucide="save" style="width: 20px; height: 20px;"></i>
                    Save Changes
                </button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();

        // Preference Logic
        const maxPreferences = 6;
        const options = document.querySelectorAll('.preference-option');
        const counter = document.getElementById('preference-counter');

        function updateCounter() {
            const selected = document.querySelectorAll('.preference-option.selected').length;
            counter.textContent = `${selected}/${maxPreferences} selected`;
        }

        options.forEach(option => {
            option.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default label behavior to handle custom logic
                
                const isSelected = option.classList.contains('selected');
                const currentSelectedCount = document.querySelectorAll('.preference-option.selected').length;

                if (!isSelected && currentSelectedCount >= maxPreferences) {
                    return; // Max limit reached
                }

                option.classList.toggle('selected');
                
                // Update visual state of custom checkbox
                const checkboxVisual = option.querySelector('.custom-checkbox');
                const checkIcon = checkboxVisual.querySelector('i');
                
                if (option.classList.contains('selected')) {
                    checkboxVisual.style.backgroundColor = '#2563eb';
                    checkboxVisual.style.borderColor = '#2563eb';
                    checkIcon.style.opacity = '1';
                } else {
                    checkboxVisual.style.backgroundColor = 'transparent';
                    checkboxVisual.style.borderColor = '#d1d5db';
                    checkIcon.style.opacity = '0';
                }

                updateCounter();
            });
        });
    </script>
</body>
</html>
