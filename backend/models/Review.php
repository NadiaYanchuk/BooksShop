<?php
/**
 * Модель отзыва
 */
class Review {
    private $conn;
    private $table_name = "reviews";

    public $id;
    public $product_id;
    public $name;
    public $email;
    public $rating;
    public $comment;
    public $is_approved;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Создать отзыв (публичная форма)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (product_id, name, email, rating, comment) 
                  VALUES (:product_id, :name, :email, :rating, :comment)";

        $stmt = $this->conn->prepare($query);

        // Очистка данных
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->comment = htmlspecialchars(strip_tags($this->comment));

        // Валидация
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($this->rating < 1 || $this->rating > 5) {
            return false;
        }

        // Привязка параметров
        $stmt->bindParam(":product_id", $this->product_id);
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":rating", $this->rating);
        $stmt->bindParam(":comment", $this->comment);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Получить одобренные отзывы для продукта (публичный доступ)
     */
    public function readByProduct($product_id) {
        $query = "SELECT r.id, r.name, r.rating, r.comment, r.created_at, p.name as product_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN products p ON r.product_id = p.id
                  WHERE r.product_id = :product_id AND r.is_approved = 1
                  ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":product_id", $product_id);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Получить все одобренные отзывы (для главной страницы)
     */
    public function readApproved() {
        $query = "SELECT r.id, r.name, r.rating, r.comment, r.created_at, p.name as product_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN products p ON r.product_id = p.id
                  WHERE r.is_approved = 1
                  ORDER BY r.created_at DESC
                  LIMIT 20";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Получить все отзывы (для администратора)
     */
    public function readAll($filters = []) {
        $query = "SELECT r.id, r.product_id, r.name, r.email, r.rating, r.comment, r.is_approved, r.created_at, p.name as product_name
                  FROM " . $this->table_name . " r
                  LEFT JOIN products p ON r.product_id = p.id
                  WHERE 1=1";

        // Применение фильтров
        if (isset($filters['is_approved']) && $filters['is_approved'] !== '') {
            $query .= " AND r.is_approved = :is_approved";
        }
        if (!empty($filters['product_id'])) {
            $query .= " AND r.product_id = :product_id";
        }
        if (!empty($filters['rating'])) {
            $query .= " AND r.rating = :rating";
        }

        $query .= " ORDER BY r.created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Привязка параметров фильтров
        if (isset($filters['is_approved']) && $filters['is_approved'] !== '') {
            $stmt->bindParam(":is_approved", $filters['is_approved']);
        }
        if (!empty($filters['product_id'])) {
            $stmt->bindParam(":product_id", $filters['product_id']);
        }
        if (!empty($filters['rating'])) {
            $stmt->bindParam(":rating", $filters['rating']);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Обновить статус одобрения отзыва
     */
    public function updateApproval() {
        $query = "UPDATE " . $this->table_name . " 
                  SET is_approved = :is_approved 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        
        // Преобразование boolean в integer для MySQL
        $is_approved_int = $this->is_approved ? 1 : 0;
        
        $stmt->bindParam(":is_approved", $is_approved_int, PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Удалить отзыв
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }
}
