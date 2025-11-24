<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * Listing Model
 * Handles property listings and images
 */
class Listing extends BaseModel {
    protected $table = 'listings';

    /**
     * Get all available listings
     * @return array
     */
    public function getAvailable() {
        return $this->getAll(['availability_status' => 'available'], 'created_at DESC');
    }

    /**
     * Get listings by landlord
     * @param int $landlordId
     * @return array
     */
    public function getByLandlord($landlordId) {
        return $this->getAll(['landlord_id' => $landlordId], 'created_at DESC');
    }

    /**
     * Get listing with images
     * @param int $listingId
     * @return array|false
     */
    public function getWithImages($listingId) {
        $listing = $this->getById($listingId);
        
        if (!$listing) {
            return false;
        }

        // Parse JSON fields
        if ($listing['amenities']) {
            $listing['amenities'] = json_decode($listing['amenities'], true);
        }
        if ($listing['house_rules_data']) {
            $listing['house_rules_data'] = json_decode($listing['house_rules_data'], true);
        }

        // Get images
        $sql = "SELECT * FROM listing_images WHERE listing_id = :listing_id ORDER BY is_primary DESC, image_id ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->execute();
        $listing['images'] = $stmt->fetchAll();

        // Get landlord info
        $sql = "SELECT user_id, first_name, last_name, email, phone, profile_photo FROM users WHERE user_id = :landlord_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $listing['landlord_id'], PDO::PARAM_INT);
        $stmt->execute();
        $listing['landlord'] = $stmt->fetch();

        return $listing;
    }

    /**
     * Create listing with images
     * @param array $listingData
     * @param array $imageUrls
     * @return int|false
     */
    public function createWithImages($listingData, $imageUrls = []) {
        // Handle JSON fields
        if (isset($listingData['amenities']) && is_array($listingData['amenities'])) {
            $listingData['amenities'] = json_encode($listingData['amenities']);
        }
        if (isset($listingData['house_rules_data']) && is_array($listingData['house_rules_data'])) {
            $listingData['house_rules_data'] = json_encode($listingData['house_rules_data']);
        }

        $listingId = $this->create($listingData);
        
        if ($listingId && !empty($imageUrls)) {
            foreach ($imageUrls as $index => $url) {
                $this->addImage($listingId, $url, $index === 0);
            }
        }

        return $listingId;
    }

    /**
     * Add image to listing
     * @param int $listingId
     * @param string $imageUrl
     * @param bool $isPrimary
     * @return bool
     */
    public function addImage($listingId, $imageUrl, $isPrimary = false) {
        $sql = "INSERT INTO listing_images (listing_id, image_url, is_primary) 
                VALUES (:listing_id, :image_url, :is_primary)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':listing_id', $listingId, PDO::PARAM_INT);
        $stmt->bindValue(':image_url', $imageUrl);
        $stmt->bindValue(':is_primary', $isPrimary ? 1 : 0, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Search listings with filters
     * @param array $filters
     * @return array
     */
    public function search($filters = []) {
        $sql = "SELECT l.*, 
                    (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image,
                    CONCAT(u.first_name, ' ', u.last_name) as landlord_name
                FROM {$this->table} l
                LEFT JOIN users u ON l.landlord_id = u.user_id
                WHERE l.availability_status = 'available'";
        
        $params = [];

        if (!empty($filters['min_price'])) {
            $sql .= " AND l.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $sql .= " AND l.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        if (!empty($filters['location'])) {
            $sql .= " AND l.location LIKE :location";
            $params[':location'] = '%' . $filters['location'] . '%';
        }

        if (!empty($filters['room_type'])) {
            $sql .= " AND l.room_type = :room_type";
            $params[':room_type'] = $filters['room_type'];
        }

        if (!empty($filters['bedrooms'])) {
            $sql .= " AND l.bedrooms >= :bedrooms";
            $params[':bedrooms'] = $filters['bedrooms'];
        }

        $sql .= " ORDER BY l.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get listing statistics
     * @return array
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_listings,
                    SUM(CASE WHEN availability_status = 'available' THEN 1 ELSE 0 END) as available_listings,
                    SUM(CASE WHEN availability_status = 'occupied' THEN 1 ELSE 0 END) as occupied_listings,
                    SUM(CASE WHEN availability_status = 'pending' THEN 1 ELSE 0 END) as pending_listings,
                    AVG(price) as average_price
                FROM {$this->table}";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get landlord statistics
     * @param int $landlordId
     * @return array
     */
    public function getLandlordStats($landlordId) {
        $sql = "SELECT 
                    COUNT(*) as total_listings,
                    SUM(CASE WHEN availability_status = 'available' THEN 1 ELSE 0 END) as active_listings,
                    SUM(CASE WHEN availability_status = 'occupied' THEN 1 ELSE 0 END) as occupied_listings
                FROM {$this->table}
                WHERE landlord_id = :landlord_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':landlord_id', $landlordId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get recent listings
     * @param int $limit
     * @return array
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get pending listings
     * @param int $limit
     * @return array
     */
    public function getPending($limit = 5) {
        $sql = "SELECT * FROM {$this->table} WHERE availability_status = 'pending' ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
