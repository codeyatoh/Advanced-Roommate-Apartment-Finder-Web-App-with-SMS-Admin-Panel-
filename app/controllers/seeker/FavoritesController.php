<?php
require_once __DIR__ . '/../../models/SavedListing.php';

class FavoritesController {
    private $savedListingModel;

    public function __construct() {
        $this->savedListingModel = new SavedListing();
    }

    public function toggle() {
        // Ensure POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }

        // Ensure user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        // Get input data
        $data = json_decode(file_get_contents('php://input'), true);
        $listingId = $data['listing_id'] ?? null;

        if (!$listingId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Listing ID required']);
            return;
        }

        try {
            $action = $this->savedListingModel->toggle($_SESSION['user_id'], $listingId);
            echo json_encode([
                'success' => true, 
                'action' => $action,
                'message' => $action === 'added' ? 'Listing saved' : 'Listing removed'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error']);
        }
    }
}

// Handle request if called directly
if (basename($_SERVER['PHP_SELF']) === 'FavoritesController.php') {
    $controller = new FavoritesController();
    $controller->toggle();
}
