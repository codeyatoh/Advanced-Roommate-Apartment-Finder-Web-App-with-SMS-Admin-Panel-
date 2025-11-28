<?php
require_once __DIR__ . '/BaseModel.php';

class SavedListing extends BaseModel {
    protected $table = 'saved_listings';

    protected function getPrimaryKey() {
        return 'save_id';
    }

    public function toggle($userId, $listingId) {
        // Check if already saved
        $sql = "SELECT save_id FROM {$this->table} WHERE user_id = :user_id AND listing_id = :listing_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            // Remove
            $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND listing_id = :listing_id";
            $action = 'removed';
        } else {
            // Add
            $sql = "INSERT INTO {$this->table} (user_id, listing_id) VALUES (:user_id, :listing_id)";
            $action = 'added';
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();

        return $action;
    }

    public function isSaved($userId, $listingId) {
        $sql = "SELECT 1 FROM {$this->table} WHERE user_id = :user_id AND listing_id = :listing_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function getSavedListings($userId) {
        $sql = "SELECT l.*, 
                       (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image
                FROM listings l
                JOIN {$this->table} s ON l.listing_id = s.listing_id
                WHERE s.user_id = :user_id
                ORDER BY s.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getCount($userId) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int)$result['count'];
    }
}
