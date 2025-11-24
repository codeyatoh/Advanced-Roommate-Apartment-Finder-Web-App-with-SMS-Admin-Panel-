<?php
/**
 * Database Connection Class
 * Handles PDO connection to MySQL database
 */
class Database {
    private $host = 'localhost';
    private $db_name = 'roomfinder';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Establish database connection
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }

    /**
     * Get current connection (auto-connects if not connected)
     * @return PDO
     */
    public function getConnection() {
        if ($this->conn === null) {
            $this->connect();
        }
        return $this->conn;
    }
}
