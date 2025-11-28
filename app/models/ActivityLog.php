<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * ActivityLog Model
 * Handles tracking of user activities
 * 
 * Table Schema:
 * - activity_id (PK)
 * - user_id (FK)
 * - action (string) e.g., 'login', 'view_listing', 'book_appointment'
 * - description (text)
 * - created_at (datetime)
 */
class ActivityLog extends BaseModel {
    protected $table = 'activity_logs';

    protected function getPrimaryKey() {
        return 'activity_id';
    }

    /**
     * Log a new activity
     * @param int $userId
     * @param string $action
     * @param string $description
     * @return bool|int
     */
    public function log($userId, $action, $description) {
        return $this->create([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description
        ]);
    }

    /**
     * Get recent activities for a user
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getRecent($userId, $limit = 5) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all recent activities (for admin)
     * @param int $limit
     * @return array
     */
    public function getAllRecent($limit = 10) {
        $sql = "SELECT a.*, u.first_name, u.last_name, u.role 
                FROM {$this->table} a
                JOIN users u ON a.user_id = u.user_id
                ORDER BY a.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
