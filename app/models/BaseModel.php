<?php
require_once __DIR__ . '/../config/Database.php';

/**
 * Base Model Class
 * Provides common CRUD operations for all models
 */
abstract class BaseModel {
    protected $db;
    protected $conn;
    protected $table;

    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->connect();
    }

    /**
     * Get all records from table
     * @param array $conditions Optional WHERE conditions
     * @param string $orderBy Optional ORDER BY clause
     * @return array
     */
    public function getAll($conditions = [], $orderBy = '') {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_map(fn($k) => "$k = :$k", array_keys($conditions)));
        }
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        $stmt = $this->conn->prepare($sql);
        
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get single record by ID
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Create new record
     * @param array $data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Update existing record
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE {$this->getPrimaryKey()} = :id";
        $stmt = $this->conn->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Delete record
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->getPrimaryKey()} = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Get primary key column name
     * Override in child classes if different
     * @return string
     */
    protected function getPrimaryKey() {
        return $this->table . '_id';
    }

    /**
     * Execute custom query
     * @param string $sql
     * @param array $params
     * @return PDOStatement|false
     */
    protected function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
