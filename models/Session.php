<?php
require_once __DIR__ . '/../config/database.php';

class Session {
    private $conn;
    private $table_name = "sessions";

    public $id;
    public $user_id;
    public $created_at;
    public $expires_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create($user_id) {
        // Clean up expired sessions first
        $this->cleanExpired();
        
        $this->id = bin2hex(random_bytes(64));
        $this->user_id = $user_id;
        $this->expires_at = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);

        $query = "INSERT INTO " . $this->table_name . " 
                  SET id=:id, user_id=:user_id, expires_at=:expires_at";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":expires_at", $this->expires_at);

        if ($stmt->execute()) {
            $_SESSION['session_id'] = $this->id;
            $_SESSION['user_id'] = $this->user_id;
            return true;
        }
        return false;
    }

    public function validate($session_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id = :id AND expires_at > NOW() LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $session_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->created_at = $row['created_at'];
            $this->expires_at = $row['expires_at'];
            return true;
        }
        return false;
    }

    public function destroy($session_id = null) {
        if ($session_id === null && isset($_SESSION['session_id'])) {
            $session_id = $_SESSION['session_id'];
        }
        
        if ($session_id) {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $session_id);
            $stmt->execute();
        }
        
        session_destroy();
    }

    public function cleanExpired() {
        $query = "DELETE FROM " . $this->table_name . " WHERE expires_at < NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }

    public function extend($session_id) {
        $new_expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $query = "UPDATE " . $this->table_name . " 
                  SET expires_at = :expires_at WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":expires_at", $new_expires);
        $stmt->bindParam(":id", $session_id);
        
        return $stmt->execute();
    }
}
?>