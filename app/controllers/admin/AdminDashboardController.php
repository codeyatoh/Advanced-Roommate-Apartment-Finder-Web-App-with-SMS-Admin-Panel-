<?php
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Listing.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

class AdminDashboardController {
    private $userModel;
    private $listingModel;
    private $reportModel;
    private $activityLogModel;

    public function __construct() {
        $this->userModel = new User();
        $this->listingModel = new Listing();
        $this->reportModel = new Report();
        $this->activityLogModel = new ActivityLog();
    }

    public function getDashboardData() {
        // Fetch stats
        $userStats = $this->userModel->getStats();
        $listingStats = $this->listingModel->getStats();
        $reportStats = $this->reportModel->getStats();

        // Fetch recent activity (System-wide)
        $recentActivity = $this->activityLogModel->getAllRecent(10);

        // Fetch pending actions
        $pendingActions = [];

        // Pending Listings
        $pendingListings = $this->listingModel->getPending(5);
        if ($pendingListings) {
            foreach ($pendingListings as $pl) {
                $landlord = $this->userModel->getById($pl['landlord_id']);
                $landlordName = ($landlord) ? $landlord['first_name'] . ' ' . $landlord['last_name'] : 'Unknown';
                $pendingActions[] = [
                    'id' => $pl['listing_id'],
                    'type' => 'Listing Approval',
                    'description' => $pl['title'] . ' - ' . $landlordName,
                    'priority' => 'high',
                    'link' => 'listings.php?status=pending'
                ];
            }
        }

        // Unverified Landlords
        $unverifiedUsers = $this->userModel->getUnverifiedLandlords(5);
        if ($unverifiedUsers) {
            foreach ($unverifiedUsers as $uu) {
                $pendingActions[] = [
                    'id' => $uu['user_id'],
                    'type' => 'User Verification',
                    'description' => $uu['first_name'] . ' ' . $uu['last_name'] . ' - Landlord Account',
                    'priority' => 'medium',
                    'link' => 'users.php?role=landlord&status=unverified'
                ];
            }
        }

        // Pending Reports
        $pendingReports = $this->reportModel->getPending(5);
        if ($pendingReports) {
            foreach ($pendingReports as $pr) {
                $pendingActions[] = [
                    'id' => $pr['report_id'],
                    'type' => 'Complaint Review',
                    'description' => 'Report #' . $pr['report_id'] . ' - ' . ($pr['reason'] ?? 'No reason provided'),
                    'priority' => 'high',
                    'link' => 'reports.php?status=pending'
                ];
            }
        }

        return [
            'stats' => [
                'users' => $userStats,
                'listings' => $listingStats,
                'reports' => $reportStats
            ],
            'recent_activity' => $recentActivity,
            'pending_actions' => array_slice($pendingActions, 0, 10) // Limit to 10
        ];
    }
}
