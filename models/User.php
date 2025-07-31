<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $email;
    public $company_id;
    public $role;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET username=:username, password=:password, email=:email, 
                      company_id=:company_id, role=:role";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->password = password_hash($this->password, PASSWORD_DEFAULT);
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->company_id = htmlspecialchars(strip_tags($this->company_id));
        $this->role = htmlspecialchars(strip_tags($this->role));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":password", $this->password);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":company_id", $this->company_id);
        $stmt->bindParam(":role", $this->role);

        return $stmt->execute();
    }

    public function login($username, $password) {
        $query = "SELECT id, username, password, email, company_id, role 
                  FROM " . $this->table_name . " 
                  WHERE username = :username LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                $this->company_id = $row['company_id'];
                $this->role = $row['role'];
                return true;
            }
        }
        return false;
    }

    public function findById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->company_id = $row['company_id'];
            $this->role = $row['role'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET username=:username, email=:email, company_id=:company_id, role=:role 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->company_id = htmlspecialchars(strip_tags($this->company_id));
        $this->role = htmlspecialchars(strip_tags($this->role));

        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":company_id", $this->company_id);
        $stmt->bindParam(":role", $this->role);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function getAllByCompany($company_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE company_id = :company_id ORDER BY username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_id", $company_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username) {
        $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>