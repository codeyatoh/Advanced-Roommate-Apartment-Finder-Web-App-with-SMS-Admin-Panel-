<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Report Model
 * Handles user/listing reports
 */
class Report extends BaseModel {
    protected $table = 'reports';

    /**
     * Get all reports with details
     * @param string $status Optional filter by status
     * @return array
     */
    public function getAll($conditions = [], $orderBy = 'created_at DESC') {
        $sql = "SELECT 
                    r.*,
                    CONCAT(reporter.first_name, ' ', reporter.last_name) as reporter_name,
                    CONCAT(reported_user.first_name, ' ', reported_user.last_name) as reported_user_name,
                    l.title as reported_listing_title
                FROM {$this->table} r
                INNER JOIN users reporter ON r.reporter_id = reporter.user_id
                LEFT JOIN users reported_user ON r.reported_user_id = reported_user.user_id
                LEFT JOIN listings l ON r.reported_listing_id = l.listing_id";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($k) => "r.$k = :$k", array_keys($conditions)));
        }
        
        $sql .= " ORDER BY r.{$orderBy}";

        $stmt = $this->conn->prepare($sql);
        
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get pending reports count
     * @return int
     */
    public function getPendingCount() {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE status = 'pending'";
        
        $stmt = $this->conn->query($sql);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    /**
     * Update report status
     * @param int $reportId
     * @param string $status
     * @return bool
     */
    public function updateStatus($reportId, $status) {
        $validStatuses = ['pending', 'resolved', 'dismissed'];
        
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->update($reportId, ['status' => $status]);
    }

    /**
     * Get report statistics
     * @return array
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reports,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_reports,
                    SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) as dismissed_reports,
                    SUM(CASE WHEN report_type = 'listing' THEN 1 ELSE 0 END) as listing_reports,
                    SUM(CASE WHEN report_type = 'user' THEN 1 ELSE 0 END) as user_reports,
                    SUM(CASE WHEN report_type = 'message' THEN 1 ELSE 0 END) as message_reports
                FROM {$this->table}";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get reports by type
     * @param string $type
     * @return array
     */
    public function getByType($type) {
        return $this->getAll(['report_type' => $type]);
    }
}
