<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

ob_start();
session_start();
header('Content-Type: application/json');

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR || $error['type'] === E_COMPILE_ERROR)) {
        if (ob_get_length()) ob_clean();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Fatal Error: ' . $error['message']]);
    }
});

try {

require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Notification.php';

$listingModel = new Listing();
$notificationModel = new Notification();

$action = $_GET['action'] ?? '';

if ($action === 'create') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed', 405);
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
        throw new Exception('Unauthorized', 403);
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? floatval($_POST['price']) : null;
    $location = trim($_POST['location'] ?? '');

    if ($title === '' || $description === '' || $price === null || $location === '') {
        throw new Exception('Please complete all required fields.');
    }

    $houseRules = [];
    if (!empty($_POST['house_rules']) && is_array($_POST['house_rules'])) {
        foreach ($_POST['house_rules'] as $ruleKey => $value) {
            if (in_array($ruleKey, ['pets_details', 'quiet_hours_start', 'quiet_hours_end'], true)) {
                if ($value !== '') {
                    $houseRules[$ruleKey] = trim($value);
                }
                continue;
            }
            $houseRules[$ruleKey] = true;
        }
    }

    $listingData = [
        'landlord_id' => $_SESSION['user_id'],
        'title' => $title,
        'description' => $description,
        'price' => $price,
        'security_deposit' => isset($_POST['security_deposit']) ? floatval($_POST['security_deposit']) : null,
        'location' => $location,
        'available_from' => $_POST['available_from'] ?? null,
        'utilities_included' => isset($_POST['utilities_included']) ? 1 : 0,
        'room_type' => $_POST['room_type'] ?? 'private_room',
        'bedrooms' => isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : null,
        'bathrooms' => isset($_POST['bathrooms']) ? floatval($_POST['bathrooms']) : null,
        'current_roommates' => isset($_POST['current_roommates']) ? intval($_POST['current_roommates']) : null,
        'amenities' => isset($_POST['amenities']) ? array_map('trim', $_POST['amenities']) : [],
        'house_rules_data' => $houseRules,
        'availability_status' => 'pending',
        'approval_status' => 'pending'
    ];

    $imageUrls = [];
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $uploadDir = __DIR__ . '/../../public/uploads/listings/';
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                throw new Exception("Failed to create upload directory.");
            }
        }

        foreach ($_FILES['images']['name'] as $key => $name) {
            if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['images']['tmp_name'][$key];
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid('listing_') . '.' . $extension;
                
                if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                    $imageUrls[] = '/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/uploads/listings/' . $filename;
                } else {
                    throw new Exception("Failed to upload image: $name");
                }
            } elseif ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                 throw new Exception("Image upload error code: " . $_FILES['images']['error'][$key]);
            }
        }
    }

    $listingId = $listingModel->createWithImages($listingData, $imageUrls);

    if ($listingId) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Listing submitted for review. You will be notified once an admin approves it.'
        ]);
    } else {
        throw new Exception("Failed to save listing. Please try again.");
    }
    exit;
}

// ... (skipping to update action)

if ($action === 'update') {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Method not allowed', 405);
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
            throw new Exception('Unauthorized', 403);
        }

        $listingId = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        if (!$listingId) {
            throw new Exception('Invalid listing ID.', 400);
        }

        // Verify ownership
        $existingListing = $listingModel->getById($listingId);
        if (!$existingListing || $existingListing['landlord_id'] !== $_SESSION['user_id']) {
            throw new Exception('Access denied.', 403);
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = isset($_POST['price']) ? floatval($_POST['price']) : null;
        $location = trim($_POST['location'] ?? '');

        if ($title === '' || $description === '' || $price === null || $location === '') {
            throw new Exception('Please complete all required fields.');
        }

        $houseRules = [];
        if (!empty($_POST['house_rules']) && is_array($_POST['house_rules'])) {
            foreach ($_POST['house_rules'] as $ruleKey => $value) {
                if (in_array($ruleKey, ['pets_details', 'quiet_hours_start', 'quiet_hours_end'], true)) {
                    if ($value !== '') {
                        $houseRules[$ruleKey] = trim($value);
                    }
                    continue;
                }
                $houseRules[$ruleKey] = true;
            }
        }

        $listingData = [
            'landlord_id' => $_SESSION['user_id'],
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'security_deposit' => isset($_POST['security_deposit']) ? floatval($_POST['security_deposit']) : null,
            'location' => $location,
            'available_from' => $_POST['available_from'] ?? null,
            'utilities_included' => isset($_POST['utilities_included']) ? 1 : 0,
            'room_type' => $_POST['room_type'] ?? 'private_room',
            'bedrooms' => isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : null,
            'bathrooms' => isset($_POST['bathrooms']) ? floatval($_POST['bathrooms']) : null,
            'current_roommates' => isset($_POST['current_roommates']) ? intval($_POST['current_roommates']) : null,
            'amenities' => isset($_POST['amenities']) ? array_map('trim', $_POST['amenities']) : [],
            'house_rules_data' => $houseRules
        ];

        // Handle Image Uploads
        $newImageUrls = [];
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $uploadDir = __DIR__ . '/../../public/uploads/listings/';
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception("Failed to create upload directory.");
                }
            }

            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $tmpName = $_FILES['images']['tmp_name'][$key];
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $filename = uniqid('listing_') . '.' . $extension;
                    
                    if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
                        $newImageUrls[] = '/Advanced-Roommate-Apartment-Finder-Web-App-with-Email-Admin-Panel-/public/uploads/listings/' . $filename;
                    } else {
                        throw new Exception("Failed to upload image: $name");
                    }
                } elseif ($_FILES['images']['error'][$key] !== UPLOAD_ERR_NO_FILE) {
                     // Ignore NO_FILE error, but throw for others
                     throw new Exception("Image upload error code: " . $_FILES['images']['error'][$key]);
                }
            }
        }

        // Get existing image IDs to keep
        $existingImageIds = isset($_POST['existing_images']) ? array_map('intval', $_POST['existing_images']) : [];

        if ($listingModel->updateWithImages($listingId, $listingData, $newImageUrls, $existingImageIds)) {
            ob_clean();
            echo json_encode([
                'success' => true,
                'message' => 'Listing updated successfully. It has been submitted for admin approval.'
            ]);
        } else {
            throw new Exception("Failed to update listing in database.");
        }

    } catch (Throwable $e) {
        ob_clean();
        $code = $e->getCode();
        if ($code < 100 || $code > 599) $code = 500;
        http_response_code($code);
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    exit;
}


if ($action === 'updateStatus') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $listingId = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
    $status = $_POST['status'] ?? '';
    $adminNote = trim($_POST['note'] ?? '');

    if (!$listingId || !in_array($status, ['approved', 'rejected'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
        exit;
    }

    $listing = $listingModel->getById($listingId);
    if (!$listing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Listing not found.']);
        exit;
    }

    $listingModel->updateApprovalStatus($listingId, $status, $_SESSION['user_id'], $adminNote);

    // Notify landlord
    $notificationModel->create([
        'user_id' => $listing['landlord_id'],
        'type' => $status === 'approved' ? 'listing_approved' : 'listing_rejected',
        'title' => $status === 'approved' ? 'Listing Approved' : 'Listing Rejected',
        'message' => $status === 'approved'
            ? "Great news! Your listing \"{$listing['title']}\" has been approved and is now visible to seekers."
            : ("Your listing \"{$listing['title']}\" was rejected. " . ($adminNote ?: 'Please review and update it.')),
        'related_id' => $listingId,
        'related_user_id' => $_SESSION['user_id']
    ]);

    echo json_encode([
        'success' => true,
        'message' => $status === 'approved' ? 'Listing approved.' : 'Listing rejected.',
        'status' => $status
    ]);
    exit;
}

if ($action === 'delete') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'landlord') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $listingId = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
    if (!$listingId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid listing ID.']);
        exit;
    }

    // Verify ownership
    $existingListing = $listingModel->getById($listingId);
    if (!$existingListing || $existingListing['landlord_id'] !== $_SESSION['user_id']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied.']);
        exit;
    }

    if ($listingModel->deleteWithImages($listingId)) {
        echo json_encode(['success' => true, 'message' => 'Listing deleted successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete listing.']);
    }
    exit;
}

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unknown action.']);

} catch (Throwable $e) {
    if (ob_get_length()) ob_clean();
    $code = $e->getCode();
    if ($code < 100 || $code > 599) $code = 500;
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

