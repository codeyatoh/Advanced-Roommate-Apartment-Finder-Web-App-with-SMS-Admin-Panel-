<?php
require_once __DIR__ . '/../../models/Listing.php';
require_once __DIR__ . '/../../models/Message.php';
require_once __DIR__ . '/../../models/Appointment.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

class DashboardController {
    private $listingModel;
    private $messageModel;
    private $appointmentModel;
    private $userModel;
    private $activityLogModel;

    public function __construct() {
        $this->listingModel = new Listing();
        $this->messageModel = new Message();
        $this->appointmentModel = new Appointment();
        $this->userModel = new User();
        $this->activityLogModel = new ActivityLog();
    }

    public function getDashboardData($landlordId) {
        // Fetch stats
        $landlordStats = $this->listingModel->getLandlordStats($landlordId);
        $pendingAppointmentsCount = $this->appointmentModel->getPendingCount($landlordId);
        $unreadMessagesCount = $this->messageModel->getUnreadCount($landlordId);

        // Fetch content
        $pendingViewings = $this->appointmentModel->getPendingForLandlord($landlordId);
        $recentInquiries = $this->messageModel->getLandlordInquiries($landlordId);
        $recentActivity = $this->activityLogModel->getRecent($landlordId, 5);

        // Calculate performance metrics
        $monthlyRevenue = 0;
        $activeListings = $this->listingModel->getByLandlord($landlordId);
        $totalListings = count($activeListings);
        $occupiedListings = 0;

        foreach ($activeListings as $listing) {
            $status = $listing['availability_status'] ?? 'available';
            if ($status === 'occupied' || $status === 'rented') {
                $monthlyRevenue += floatval($listing['price']);
                $occupiedListings++;
            }
        }
        
        $occupancyRate = $totalListings > 0 ? round(($occupiedListings / $totalListings) * 100) : 0;

        return [
            'stats' => [
                'active_listings' => $landlordStats['active_listings'] ?? 0,
                'total_listings' => $landlordStats['total_listings'] ?? 0,
                'new_inquiries' => $unreadMessagesCount,
                'pending_viewings' => $pendingAppointmentsCount
            ],
            'pending_viewings' => $pendingViewings,
            'recent_inquiries' => array_slice($recentInquiries, 0, 5),
            'recent_activity' => $recentActivity,
            'performance' => [
                'monthly_revenue' => $monthlyRevenue,
                'occupancy_rate' => $occupancyRate,
                'occupied_count' => $occupiedListings,
                'total_count' => $totalListings
            ]
        ];
    }
}
