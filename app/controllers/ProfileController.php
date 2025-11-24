<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/SeekerProfile.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userModel = new User();
$profileModel = new SeekerProfile();

// Handle GET request for profile completion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_completion') {
    $completion = $userModel->getProfileCompletion($userId);
    echo json_encode(['completion' => $completion['percentage']]);
    exit;
}

// Handle POST request to save profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle password change if provided
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $currentUser = $userModel->getById($userId);
            
            // Verify current password
            if (!password_verify($_POST['current_password'], $currentUser['password'])) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ]);
                exit;
            }
            
            // Validate new password
            if (strlen($_POST['new_password']) < 8) {
                echo json_encode([
                    'success' => false,
                    'message' => 'New password must be at least 8 characters'
                ]);
                exit;
            }
            
            // Check if passwords match
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                echo json_encode([
                    'success' => false,
                    'message' => 'New passwords do not match'
                ]);
                exit;
            }
            
            // Update password
            $userModel->update($userId, [
                'password' => password_hash($_POST['new_password'], PASSWORD_DEFAULT)
            ]);
        }

        // Prepare user data
        $userData = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'gender' => $_POST['gender'] ?? null
        ];

        // Handle photo upload
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $uploadPath)) {
                $userData['profile_photo'] = '/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/uploads/profiles/' . $filename;
            }
        }

        // Update user table
        $userModel->update($userId, $userData);

        // Prepare seeker profile data
        $profileData = [
            'user_id' => $userId,
            'occupation' => $_POST['occupation'] ?? '',
            'preferred_location' => $_POST['location'] ?? '',
            'budget' => !empty($_POST['budget']) ? (float)$_POST['budget'] : null,
            'move_in_date' => !empty($_POST['move_in_date']) ? $_POST['move_in_date'] : null,
            'sleep_schedule' => $_POST['sleep_schedule'] ?? null,
            'social_level' => $_POST['social_level'] ?? null,
            'guests_preference' => $_POST['guests'] ?? null,
            'cleanliness' => $_POST['cleanliness'] ?? null,
            'work_schedule' => $_POST['work_schedule'] ?? null,
            'noise_level' => $_POST['noise_level'] ?? null,
            'preferences' => isset($_POST['preferences']) ? json_encode($_POST['preferences']) : '[]'
        ];

        // Check if profile exists
        $existingProfile = $profileModel->getByUserId($userId);
        
        if ($existingProfile) {
            // Update existing profile
            $profileModel->update($existingProfile['profile_id'], $profileData);
        } else {
            // Create new profile
            $profileModel->create($profileData);
        }

        // Get updated profile completion
        $completion = $userModel->getProfileCompletion($userId);

        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully',
            'completion' => $completion['percentage']
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating profile: ' . $e->getMessage()
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
