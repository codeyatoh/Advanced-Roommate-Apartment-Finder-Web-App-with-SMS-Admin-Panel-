<?php
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Listing.php';
require_once __DIR__ . '/../../models/Message.php';
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../models/SavedListing.php';
require_once __DIR__ . '/../../models/Match.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

class DashboardController {
    private $userModel;
    private $listingModel;
    private $messageModel;
    private $appointmentModel;
    private $savedListingModel;
    private $matchModel;
    private $activityLogModel;

    public function __construct() {
        $this->userModel = new User();
        $this->listingModel = new Listing();
        $this->messageModel = new Message();
        $this->appointmentModel = new Appointment();
        $this->savedListingModel = new SavedListing();
        $this->matchModel = new RoommateMatch();
        $this->activityLogModel = new ActivityLog();
    }

    public function getDashboardData($userId) {
        // Fetch stats
        $unreadMessages = $this->messageModel->getUnreadCount($userId);
        $upcomingAppointments = $this->appointmentModel->getUpcoming($userId, 'seeker');
        $savedCount = $this->savedListingModel->getCount($userId);
        
        // Matches count (approximate based on mutual matches)
        $matches = $this->matchModel->getMutualMatches($userId);
        $matchCount = count($matches);

        // Fetch content
        $recommendedListings = $this->listingModel->getAvailable(2); // Get 2 listings
        $savedListings = $this->savedListingModel->getSavedListings($userId);
        $recentActivity = $this->activityLogModel->getRecent($userId, 5);

        return [
            'stats' => [
                'unread_messages' => $unreadMessages,
                'upcoming_appointments' => count($upcomingAppointments),
                'saved_rooms' => $savedCount,
                'matches' => $matchCount
            ],
            'upcoming_appointments' => $upcomingAppointments,
            'recommended_listings' => $recommendedListings,
            'saved_listings' => $savedListings,
            'recent_activity' => $recentActivity,
            'matches_list' => array_slice($matches, 0, 3)
        ];
    }
}
