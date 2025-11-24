<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Notification Model
 * Handles user notifications for matches, appointments, etc.
 */
class Notification extends BaseModel {
    protected $table = 'notifications';

    /**
     * Get unread notifications for a user
     * @param int $userId
     * @return array
     */
    public function getUnreadForUser($userId) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id 
                AND is_read = 0
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get unread count for a user
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table}
                WHERE user_id = :user_id 
                AND is_read = 0";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Mark notification as read
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId) {
        return $this->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Mark all notifications as read for a user
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table}
                SET is_read = 1
                WHERE user_id = :user_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Create a new match notification
     * @param int $userId
     * @param int $matchedUserId
     * @return int|false
     */
    public function createMatchNotification($userId, $matchedUserId) {
        return $this->create([
            'user_id' => $userId,
            'type' => 'match',
            'title' => 'New Match!',
            'message' => 'You have a new roommate match',
            'related_id' => $matchedUserId,
            'is_read' => 0
        ]);
    }

    /**
     * Create appointment status notification
     * @param int $userId
     * @param int $appointmentId
     * @param string $status
     * @return int|false
     */
    public function createAppointmentNotification($userId, $appointmentId, $status) {
        $messages = [
            'confirmed' => 'Your viewing appointment has been approved',
            'declined' => 'Your viewing appointment has been declined',
            'cancelled' => 'Your viewing appointment has been cancelled'
        ];

        return $this->create([
            'user_id' => $userId,
            'type' => 'appointment',
            'title' => 'Appointment ' . ucfirst($status),
            'message' => $messages[$status] ?? 'Appointment status updated',
            'related_id' => $appointmentId,
            'is_read' => 0
        ]);
    }
}
