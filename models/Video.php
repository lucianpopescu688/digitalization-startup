<?php
require_once __DIR__ . '/../config/database.php';

class Video {
    private $conn;
    private $table_name = "videos";

    public $id;
    public $title;
    public $description;
    public $file_path;
    public $thumbnail_path;
    public $tags;
    public $uploaded_by;
    public $company_id;
    public $file_size;
    public $duration;
    public $format;
    public $created_at;
    public $updated_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, description=:description, file_path=:file_path, 
                      thumbnail_path=:thumbnail_path, tags=:tags, uploaded_by=:uploaded_by, 
                      company_id=:company_id, file_size=:file_size, duration=:duration, format=:format";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->file_path = htmlspecialchars(strip_tags($this->file_path));
        $this->thumbnail_path = htmlspecialchars(strip_tags($this->thumbnail_path));
        $this->tags = htmlspecialchars(strip_tags($this->tags));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":file_path", $this->file_path);
        $stmt->bindParam(":thumbnail_path", $this->thumbnail_path);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":uploaded_by", $this->uploaded_by);
        $stmt->bindParam(":company_id", $this->company_id);
        $stmt->bindParam(":file_size", $this->file_size);
        $stmt->bindParam(":duration", $this->duration);
        $stmt->bindParam(":format", $this->format);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function getAllByCompany($company_id, $search = '', $sort = 'created_at', $order = 'DESC') {
        $query = "SELECT v.*, u.username as uploader_name 
                  FROM " . $this->table_name . " v 
                  LEFT JOIN users u ON v.uploaded_by = u.id 
                  WHERE v.company_id = :company_id";
        
        if (!empty($search)) {
            $query .= " AND (v.title LIKE :search OR v.description LIKE :search OR v.tags LIKE :search)";
        }
        
        $query .= " ORDER BY v." . $sort . " " . $order;

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":company_id", $company_id);
        
        if (!empty($search)) {
            $search_param = "%{$search}%";
            $stmt->bindParam(":search", $search_param);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $query = "SELECT v.*, u.username as uploader_name 
                  FROM " . $this->table_name . " v 
                  LEFT JOIN users u ON v.uploaded_by = u.id 
                  WHERE v.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->file_path = $row['file_path'];
            $this->thumbnail_path = $row['thumbnail_path'];
            $this->tags = $row['tags'];
            $this->uploaded_by = $row['uploaded_by'];
            $this->company_id = $row['company_id'];
            $this->file_size = $row['file_size'];
            $this->duration = $row['duration'];
            $this->format = $row['format'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return $row;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, description=:description, tags=:tags 
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->tags = htmlspecialchars(strip_tags($this->tags));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tags", $this->tags);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        return $stmt->execute();
    }
}
?>