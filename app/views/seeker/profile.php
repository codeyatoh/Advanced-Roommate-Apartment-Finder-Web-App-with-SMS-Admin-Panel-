<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - RoomFinder</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/variables.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/globals.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/navbar.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/cards.module.css">
    <link rel="stylesheet" href="/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/assets/css/modules/forms.module.css">
</head>
<body>
    <div style="min-height: 100vh; background: linear-gradient(to bottom right, var(--softBlue-20), var(--neutral), var(--deepBlue-10));">
        <?php include __DIR__ . '/../includes/navbar.php'; ?>
        <div style="padding-top: 6rem; padding-bottom: 5rem; padding-left: 1rem; padding-right: 1rem;">
            <div style="max-width: 1024px; margin: 0 auto;">
                <div style="margin-bottom: 2rem; animation: slideUp 0.3s ease-out;">
                    <h1 style="font-size: 1.875rem; font-weight: 700; color: #000; margin-bottom: 0.5rem;">Profile Settings</h1>
                    <p style="color: rgba(0, 0, 0, 0.6);">Manage your account information and preferences</p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <!-- Profile Picture -->
                    <div class="card card-glass" style="padding: 1.25rem;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Profile Picture</h2>
                        <div style="display: flex; align-items: center; gap: 1.5rem;">
                            <div style="position: relative;">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400" alt="Profile" style="width: 5rem; height: 5rem; border-radius: 9999px; object-fit: cover; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                                <button style="position: absolute; bottom: 0; right: 0; width: 2rem; height: 2rem; background: #10b981; border-radius: 9999px; border: none; display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.08); cursor: pointer;">
                                    <i data-lucide="camera" style="width: 1rem; height: 1rem;"></i>
                                </button>
                            </div>
                            <div>
                                <button class="btn btn-primary btn-sm" style="margin-bottom: 0.5rem;">Upload New Photo</button>
                                <p style="font-size: 0.75rem; color: rgba(0,0,0,0.6); margin: 0;">JPG, PNG or GIF. Max size 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="card card-glass" style="padding: 1.25rem;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Personal Information</h2>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="user" class="form-input-icon"></i>
                                    <input type="text" class="form-input" value="John Doe">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="mail" class="form-input-icon"></i>
                                    <input type="email" class="form-input" value="john.doe@email.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="phone" class="form-input-icon"></i>
                                    <input type="tel" class="form-input" value="+1 (555) 123-4567">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Location</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="map-pin" class="form-input-icon"></i>
                                    <input type="text" class="form-input" value="San Francisco, CA">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Occupation</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="briefcase" class="form-input-icon"></i>
                                    <input type="text" class="form-input" value="Software Engineer">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- About Me -->
                    <div class="card card-glass" style="padding: 1.25rem;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">About Me</h2>
                        <textarea style="width: 100%; padding: 0.75rem 1rem; border-radius: 0.75rem; background: var(--glass-bg); color: #000; font-size: 0.875rem; border: 1px solid rgba(255,255,255,0.2); outline: none; min-height: 120px; font-family: inherit;" placeholder="Tell potential roommates about yourself...">Looking for a quiet, clean space near downtown. Non-smoker, no pets.</textarea>
                    </div>

                    <!-- Room Preferences -->
                    <div class="card card-glass" style="padding: 1.25rem;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Room Preferences</h2>
                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label class="form-label">Monthly Budget</label>
                                <div class="form-input-wrapper">
                                    <i data-lucide="home" class="form-input-icon"></i>
                                    <input type="number" class="form-input" value="1200">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Move-in Date</label>
                                <div class="form-input-wrapper">
                                    <input type="date" class="form-input" value="2024-02-01">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lifestyle Preferences -->
                    <div class="card card-glass" style="padding: 1.25rem;">
                        <h2 style="font-size: 1.125rem; font-weight: 700; color: #000; margin-bottom: 1rem;">Lifestyle Preferences</h2>
                        <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--glass-bg-subtle); border-radius: 0.5rem; cursor: pointer;">
                                <input type="checkbox" checked style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0,0,0,0.3); accent-color: #10b981; cursor: pointer;">
                                <span style="font-size: 0.875rem; color: #000; font-weight: 500;">Non-smoker</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--glass-bg-subtle); border-radius: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0,0,0,0.3); accent-color: #10b981; cursor: pointer;">
                                <span style="font-size: 0.875rem; color: #000; font-weight: 500;">Pet-friendly</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--glass-bg-subtle); border-radius: 0.5rem; cursor: pointer;">
                                <input type="checkbox" checked style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0,0,0,0.3); accent-color: #10b981; cursor: pointer;">
                                <span style="font-size: 0.875rem; color: #000; font-weight: 500;">Quiet environment</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; background: var(--glass-bg-subtle); border-radius: 0.5rem; cursor: pointer;">
                                <input type="checkbox" style="width: 1rem; height: 1rem; border-radius: 0.25rem; border: 1px solid rgba(0,0,0,0.3); accent-color: #10b981; cursor: pointer;">
                                <span style="font-size: 0.875rem; color: #000; font-weight: 500;">Social/outgoing</span>
                            </label>
                        </div>
                    </div>

                    <!-- Save Button -->
                    <div style="display: flex; justify-content: flex-end;">
                        <button class="btn btn-primary btn-md">
                            <i data-lucide="save" class="btn-icon"></i>
                            Save Changes
                        </button>
                    </div>
                </div>

                <style>
                    @media (min-width: 768px) {
                        .card.card-glass > div[style*="grid-template-columns: 1fr"] {
                            grid-template-columns: repeat(2, 1fr) !important;
                        }
                    }
                </style>
            </div>
        </div>
    </div>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>
