<?php
require_once __DIR__ . '/BaseModel.php';

/**
 * User Model
 * Handles user data and authentication
 */
class User extends BaseModel {
    protected $table = 'users';

    /**
     * Override primary key name (users table uses 'user_id' not 'users_id')
     * @return string
     */
    protected function getPrimaryKey() {
        return 'user_id';
    }

    /**
     * Get user by email
     * @param string $email
     * @return array|false
     */
    public function getUserByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Get user with profile data (seeker or landlord)
     * @param int $userId
     * @return array|false
     */
    public function getUserWithProfile($userId) {
        $user = $this->getById($userId);
        
        if (!$user) {
            return false;
        }

        // Load profile based on role
        if ($user['role'] === 'room_seeker') {
            $sql = "SELECT * FROM seeker_profiles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user['profile'] = $stmt->fetch() ?: [];
        } elseif ($user['role'] === 'landlord') {
            $sql = "SELECT * FROM landlord_profiles WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user['profile'] = $stmt->fetch() ?: [];
        }

        return $user;
    }

    /**
     * Get users by role
     * @param string $role
     * @return array
     */
    public function getUsersByRole($role) {
        return $this->getAll(['role' => $role], 'created_at DESC');
    }

    /**
     * Update seeker profile
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateSeekerProfile($userId, $data) {
        // Check if profile exists
        $sql = "SELECT profile_id FROM seeker_profiles WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $existing = $stmt->fetch();

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        if ($existing) {
            // Update existing profile
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $sql = "UPDATE seeker_profiles SET {$set} WHERE user_id = :user_id";
        } else {
            // Create new profile
            $data['user_id'] = $userId;
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO seeker_profiles ({$columns}) VALUES ({$placeholders})";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($data as $key => $value) {
            // Handle JSON fields
            if ($key === 'preferences' && is_array($value)) {
                $value = json_encode($value);
            }
            $stmt->bindValue(":$key", $value);
        }
        
        if (!$existing) {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Update landlord profile
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateLandlordProfile($userId, $data) {
        // Check if profile exists
        $sql = "SELECT profile_id FROM landlord_profiles WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $existing = $stmt->fetch();

        if ($existing) {
            // Update existing profile
            $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
            $sql = "UPDATE landlord_profiles SET {$set} WHERE user_id = :user_id";
        } else {
            // Create new profile
            $data['user_id'] = $userId;
            $columns = implode(', ', array_keys($data));
            $placeholders = ':' . implode(', :', array_keys($data));
            $sql = "INSERT INTO landlord_profiles ({$columns}) VALUES ({$placeholders})";
        }

        $stmt = $this->conn->prepare($sql);
        foreach ($data as $key => $value) {
            // Handle JSON fields
            if (in_array($key, ['operating_hours', 'verification_documents']) && is_array($value)) {
                $value = json_encode($value);
            }
            $stmt->bindValue(":$key", $value);
        }
        
        if (!$existing) {
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        }

        return $stmt->execute();
    }

    /**
     * Get user statistics
     * @return array
     */
    public function getStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    SUM(CASE WHEN role = 'room_seeker' THEN 1 ELSE 0 END) as total_seekers,
                    SUM(CASE WHEN role = 'landlord' THEN 1 ELSE 0 END) as total_landlords,
                    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_users,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users
                FROM {$this->table}";
        
        $stmt = $this->conn->query($sql);
        return $stmt->fetch();
    }

    /**
     * Get other room seekers (excluding current user)
     * @param int $currentUserId
     * @param int $limit
     * @return array
     */
    public function getOtherSeekers($currentUserId, $limit = 3) {
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.profile_photo, sp.occupation 
                FROM {$this->table} u
                LEFT JOIN seeker_profiles sp ON u.user_id = sp.user_id
                WHERE u.role = 'room_seeker' 
                  AND u.user_id != :current_user_id
                  AND u.is_active = 1
                ORDER BY u.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Calculate profile completion percentage for a seeker
     * @param int $userId
     * @return array ['percentage' => int, 'missing_fields' => array]
     */
    public function getProfileCompletion($userId) {
        // Get user basic info
        $user = $this->getById($userId);
        if (!$user || $user['role'] !== 'room_seeker') {
            return ['percentage' => 0, 'missing_fields' => []];
        }

        // Get seeker profile
        $sql = "SELECT * FROM seeker_profiles WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $profile = $stmt->fetch();

        $fields = [
            // Basic user fields (25%)
            'first_name' => [$user['first_name'], 'First Name'],
            'last_name' => [$user['last_name'], 'Last Name'],
            'email' => [$user['email'], 'Email'],
            'phone' => [$user['phone'], 'Phone'],
            'bio' => [$user['bio'], 'Bio'],
            
            // Profile fields (75%)
            'occupation' => [$profile['occupation'] ?? null, 'Occupation'],
            'budget' => [$profile['budget'] ?? null, 'Budget'],
            'move_in_date' => [$profile['move_in_date'] ?? null, 'Move-in Date'],
            'preferred_location' => [$profile['preferred_location'] ?? null, 'Preferred Location'],
            'sleep_schedule' => [$profile['sleep_schedule'] ?? null, 'Sleep Schedule'],
            'social_level' => [$profile['social_level'] ?? null, 'Social Level'],
            'guests_preference' => [$profile['guests_preference'] ?? null, 'Guests Preference'],
            'cleanliness' => [$profile['cleanliness'] ?? null, 'Cleanliness'],
            'work_schedule' => [$profile['work_schedule'] ?? null, 'Work Schedule'],
            'noise_level' => [$profile['noise_level'] ?? null, 'Noise Level']
        ];

        $totalFields = count($fields);
        $filledFields = 0;
        $missingFields = [];

        foreach ($fields as $key => $data) {
            list($value, $label) = $data;
            if (!empty($value)) {
                $filledFields++;
            } else {
                $missingFields[] = $label;
            }
        }

        $percentage = round(($filledFields / $totalFields) * 100);

        return [
            'percentage' => $percentage,
            'missing_fields' => $missingFields,
            'filled_count' => $filledFields,
            'total_count' => $totalFields
        ];
    }

    /**
     * Get recent users
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
     * Get unverified landlords
     * @param int $limit
     * @return array
     */
    public function getUnverifiedLandlords($limit = 5) {
        $sql = "SELECT * FROM {$this->table} WHERE is_verified = 0 AND role = 'landlord' ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
