<?php
require_once __DIR__ . '/../models/Appointment.php';
require_once __DIR__ . '/../models/Listing.php';
require_once __DIR__ . '/../models/Notification.php';

class AppointmentController {
    private $appointmentModel;
    private $listingModel;
    private $notificationModel;

    public function __construct() {
        $this->appointmentModel = new Appointment();
        $this->listingModel = new Listing();
        $this->notificationModel = new Notification();
    }

    public function handleRequest() {
        $action = $_GET['action'] ?? null;

        switch ($action) {
            case 'book':
                $this->bookAppointment();
                break;
            case 'update_status':
                $this->updateStatus();
                break;
            case 'reschedule':
                $this->reschedule();
                break;
            default:
                // Handle default or error
                break;
        }
    }

    private function bookAppointment() {
        session_start();
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            exit;
        }

        $seekerId = $_SESSION['user_id'];
        $listingId = $_POST['listing_id'] ?? null;
        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;
        $message = $_POST['message'] ?? '';

        if (!$listingId || !$date || !$time) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }

        // Get listing details to find landlord
        $listing = $this->listingModel->getById($listingId);
        if (!$listing) {
            echo json_encode(['success' => false, 'message' => 'Listing not found']);
            exit;
        }

        $landlordId = $listing['landlord_id'];

        // Check for existing pending request
        if ($this->appointmentModel->hasPendingRequest($seekerId, $listingId)) {
            echo json_encode(['success' => false, 'message' => 'You have already requested a viewing for this room.']);
            exit;
        }

        // Check for conflicts
        if ($this->appointmentModel->hasConflict($landlordId, $date, $time)) {
            echo json_encode(['success' => false, 'message' => 'This time slot is already booked. Please choose another time.']);
            exit;
        }

        // Create appointment
        $data = [
            'seeker_id' => $seekerId,
            'landlord_id' => $landlordId,
            'listing_id' => $listingId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'message' => $message,
            'status' => 'pending'
        ];

        if ($this->appointmentModel->create($data)) {
            // Notify Landlord
            $this->notificationModel->create([
                'user_id' => $landlordId,
                'type' => 'appointment_request',
                'title' => 'New Viewing Request',
                'message' => "You have a new viewing request for {$listing['title']}.",
                'link' => 'schedule.php'
            ]);

            echo json_encode(['success' => true, 'message' => 'Request Sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create appointment']);
        }
        exit;
    }

    private function updateStatus() {
        session_start();
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized');
            }

            $appointmentId = $_POST['appointment_id'] ?? null;
            $status = $_POST['status'] ?? null;

            if (!$appointmentId || !$status) {
                throw new Exception('Missing required fields');
            }

            // Check permissions
            // Landlords can set any status
            // Seekers can only set 'cancelled'
            if ($_SESSION['role'] !== 'landlord' && $status !== 'cancelled') {
                 throw new Exception('Unauthorized action');
            }

            if ($this->appointmentModel->updateStatus($appointmentId, $status)) {
                // Get appointment details to notify the other party
                $appointment = $this->appointmentModel->getById($appointmentId);
                if ($appointment) {
                    if ($_SESSION['role'] === 'landlord') {
                        // Notify Seeker
                        $title = $status === 'confirmed' ? 'Viewing Confirmed' : 'Viewing Update';
                        $message = $status === 'confirmed' 
                            ? "Your viewing request has been confirmed." 
                            : "Your viewing request status has been updated to {$status}.";
                        $targetUserId = $appointment['seeker_id'];
                        $link = 'appointments.php';
                    } else {
                        // Notify Landlord (Seeker cancelled)
                        $title = 'Viewing Cancelled';
                        $message = "A viewing request has been cancelled by the seeker.";
                        $targetUserId = $appointment['landlord_id'];
                        $link = 'schedule.php';
                    }
                    
                    $this->notificationModel->create([
                        'user_id' => $targetUserId,
                        'type' => 'appointment_update',
                        'title' => $title,
                        'message' => $message,
                        'related_id' => $appointmentId,
                        'link' => $link
                    ]);
                }

                echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
            } else {
                throw new Exception('Failed to update status');
            }
        } catch (Exception $e) {
            http_response_code(500); // Optional: set 500 status, but returning JSON with success:false is often enough
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function reschedule() {
        session_start();
        header('Content-Type: application/json');

        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('Unauthorized');
            }

            $appointmentId = $_POST['appointment_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $time = $_POST['time'] ?? null;

            if (!$appointmentId || !$date || !$time) {
                throw new Exception('Missing required fields');
            }

            $appointment = $this->appointmentModel->getById($appointmentId);
            if (!$appointment) {
                throw new Exception('Appointment not found');
            }

            // Check permissions
            if ($_SESSION['role'] === 'landlord') {
                if ($appointment['landlord_id'] != $_SESSION['user_id']) {
                    throw new Exception('Unauthorized action');
                }
            } else {
                if ($appointment['seeker_id'] != $_SESSION['user_id']) {
                    throw new Exception('Unauthorized action');
                }
            }

            // Check for conflicts (for the landlord)
            if ($this->appointmentModel->hasConflict($appointment['landlord_id'], $date, $time)) {
                throw new Exception('This time slot is already booked. Please choose another time.');
            }

            $sql = "UPDATE appointments SET appointment_date = :date, appointment_time = :time, status = 'pending' WHERE appointment_id = :id";
            $stmt = $this->appointmentModel->getConnection()->prepare($sql);
            $stmt->bindValue(':date', $date);
            $stmt->bindValue(':time', $time);
            $stmt->bindValue(':id', $appointmentId);
            
            if ($stmt->execute()) {
                // Determine who to notify
                if ($_SESSION['role'] === 'landlord') {
                    $targetUserId = $appointment['seeker_id'];
                    $title = 'Viewing Rescheduled';
                    $message = "The landlord has proposed a new time for the viewing. Please check your appointments.";
                    $link = 'appointments.php';
                } else {
                    $targetUserId = $appointment['landlord_id'];
                    $title = 'Viewing Rescheduled';
                    $message = "A viewing has been rescheduled by the seeker. Please review the new time.";
                    $link = 'schedule.php';
                }

                $this->notificationModel->create([
                    'user_id' => $targetUserId,
                    'type' => 'appointment_reschedule',
                    'title' => $title,
                    'message' => $message,
                    'related_id' => $appointmentId,
                    'link' => $link
                ]);

                echo json_encode(['success' => true, 'message' => 'Appointment rescheduled successfully!']);
            } else {
                throw new Exception('Failed to reschedule appointment');
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}

// Handle request
if (isset($_GET['action'])) {
    $controller = new AppointmentController();
    $controller->handleRequest();
}
?>
