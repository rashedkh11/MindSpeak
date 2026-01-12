<?php
class Logger {
    private $db;

    public function __construct() {
        $this->db = new mysqli('localhost', 'root', '', 'mindspeak1');
        if ($this->db->connect_error) {
            error_log("Logger DB Error: " . $this->db->connect_error);
        }
    }
    /**
     * Log an event to the database.
     * @param int|null $userId
     * @param string $action
     * @param string $status (SUCCESS/FAILURE/WARNING)
     * @param array|null $metadata (Optional additional data)
     */
    public function log($userId = null, $action = '', $status = 'SUCCESS', $metadata = null) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $metaJson = $metadata ? json_encode($metadata) : null;

        $stmt = $this->db->prepare("
            INSERT INTO `logs` 
            (`user_id`, `action`, `status`, `ip_address`, `user_agent`, `metadata`) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isssss", $userId, $action, $status, $ip, $userAgent, $metaJson);
        $stmt->execute();

        if ($stmt->error) {
            error_log("Log insert failed: " . $stmt->error);
        }

        $stmt->close();
    }

    public function __destruct() {
        $this->db->close();
    }
}