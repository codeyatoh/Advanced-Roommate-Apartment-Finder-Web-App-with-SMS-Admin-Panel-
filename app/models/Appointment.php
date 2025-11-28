<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Appointment Model
 * Handles viewing appointments/schedules
 */
class Appointment extends BaseModel {
    protected $table = 'appointments';

    /**
     * Override primary key name
     * @return string
     */
    protected function getPrimaryKey() {
        return 'appointment_id';
    }

    /**
     * Get appointments for seeker
     * @param int $seekerId
     * @return array
     */
    public function getSeekerAppointments($seekerId) {
        $sql = "SELECT 
                    a.*,
                    l.title as property_title,
                    l.location,
                    (SELECT image_url FROM listing_images WHERE listing_id = a.listing_id AND is_primary = 1 LIMIT 1) as property_image,
                    CONCAT(u.first_name, ' ', u.last_name) as landlord_name
                FROM {$this->table} a
                INNER JOIN listings l ON a.listing_id = l.listing_id
                INNER JOIN users u ON a.landlord_id = u.user_id
                WHERE a.seeker_id = :seeker_id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':seeker_id', $seekerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get appointments for landlord
     * @param int $landlordId
     * @return array
     */
    public function getLandlordAppointments($landlordId) {
        $sql = "SELECT 
                    a.*,
                    l.title as property_title,
                    CONCAT(u.first_name, ' ', u.last_name) as seeker_name,
                    u.email as seeker_email,
                    u.phone as seeker_phone
                FROM {$this->table} a
                INNER JOIN listings l ON a.listing_id = l.listing_id
                INNER JOIN users u ON a.seeker_id = u.user_id
                WHERE a.landlord_id = :landlord_id
                ORDER BY a.appointment_date DESC, a.appointment_time DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $landlordId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get pending appointments count
     * @param int $landlordId
     * @return int
     */
    public function getPendingCount($landlordId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE landlord_id = :landlord_id AND status = 'pending'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $landlordId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Get all pending appointments for a landlord with full details
     * @param int $landlordId
     * @return array
     */
    public function getPendingForLandlord($landlordId) {
        $sql = "SELECT a.*, l.title as listing_title
                FROM {$this->table} a
                INNER JOIN listings l ON a.listing_id = l.listing_id
                WHERE l.landlord_id = :landlord_id 
                AND a.status = 'pending'
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $landlordId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update appointment status
     * @param int $appointmentId
     * @param string $status
     * @return bool
     */
    public function updateStatus($appointmentId, $status) {
        $validStatuses = ['pending', 'confirmed', 'declined', 'completed', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($appointmentId, ['status' => $status]);
    }

    /**
     * Check for scheduling conflicts
     * @param int $landlordId
     * @param string $date
     * @param string $time
     * @return bool
     */
    public function hasConflict($landlordId, $date, $time) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE landlord_id = :landlord_id 
                  AND appointment_date = :date 
                  AND appointment_time = :time
                  AND status IN ('pending', 'confirmed')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $landlordId, PDO::PARAM_INT);
        $stmt->bindValue(':date', $date);
        $stmt->bindValue(':time', $time);
        $stmt->execute();
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get upcoming appointments
     * @param int $userId
     * @param string $userType 'seeker' or 'landlord'
     * @return array
     */
    public function getUpcoming($userId, $userType = 'seeker') {
        $userColumn = $userType === 'seeker' ? 'seeker_id' : 'landlord_id';
        
        $sql = "SELECT 
                    a.*,
                    l.title as property_title,
                    l.location,
                    (SELECT image_url FROM listing_images WHERE listing_id = a.listing_id AND is_primary = 1 LIMIT 1) as property_image,
                    CONCAT(u.first_name, ' ', u.last_name) as landlord_name,
                    CONCAT(s.first_name, ' ', s.last_name) as seeker_name
                FROM {$this->table} a
                INNER JOIN listings l ON a.listing_id = l.listing_id
                INNER JOIN users u ON a.landlord_id = u.user_id
                INNER JOIN users s ON a.seeker_id = s.user_id
                WHERE a.{$userColumn} = :user_id
                  AND a.appointment_date >= CURDATE()
                  AND a.status IN ('pending', 'confirmed')
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming appointments count
     * @param int $userId
     * @param string $userType 'seeker' or 'landlord'
     * @return int
     */
    public function getUpcomingCount($userId, $userType = 'seeker') {
        $userColumn = $userType === 'seeker' ? 'seeker_id' : 'landlord_id';
        
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE {$userColumn} = :user_id 
                  AND appointment_date >= CURDATE() 
                  AND status IN ('pending', 'confirmed')";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Check if seeker has a pending request for a listing
     * @param int $seekerId
     * @param int $listingId
     * @return bool
     */
    public function hasPendingRequest($seekerId, $listingId) {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE seeker_id = :seeker_id 
                  AND listing_id = :listing_id 
                  AND status = 'pending'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':seeker_id', $seekerId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get the timestamp of the latest appointment for a user
     * @param int $userId
     * @param string $userType 'seeker' or 'landlord'
     * @return int Unix timestamp
     */
    public function getLatestAppointmentTimestamp($userId, $userType = 'seeker') {
        $userColumn = $userType === 'seeker' ? 'seeker_id' : 'landlord_id';
        
        try {
            $sql = "SELECT MAX(created_at) as latest_created 
                    FROM {$this->table} 
                    WHERE {$userColumn} = :user_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result && $result['latest_created']) {
                return strtotime($result['latest_created']);
            }
        } catch (Exception $e) {
            // Fallback if created_at doesn't exist
        }
        
        return 0;
    }
}
