<?php
/**
 * Модель сессии
 */
class Session {
    private $conn;
    private $table_name = "sessions";

    public $id;
    public $admin_id;
    public $user_id;
    public $user_type;
    public $ip_address;
    public $user_agent;
    public $expires_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Создать новую сессию
     */
    public function create() {
        // Генерация уникального ID сессии
        $this->id = bin2hex(random_bytes(64));
        
        // Удаление старых сессий
        $this->deleteExpired();
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (id, admin_id, user_id, user_type, ip_address, user_agent, expires_at) 
                  VALUES (:id, :admin_id, :user_id, :user_type, :ip_address, :user_agent, :expires_at)";

        $stmt = $this->conn->prepare($query);

        // Привязка параметров
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":admin_id", $this->admin_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":user_type", $this->user_type);
        $stmt->bindParam(":ip_address", $this->ip_address);
        $stmt->bindParam(":user_agent", $this->user_agent);
        $stmt->bindParam(":expires_at", $this->expires_at);

        if($stmt->execute()) {
            return $this->id;
        }

        return false;
    }

    /**
     * Валидация сессии
     */
    public function validate($session_id) {
        $query = "SELECT admin_id, expires_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $session_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Проверка срока действия
            $now = new DateTime();
            $expires = new DateTime($row['expires_at']);
            
            if ($now < $expires) {
                $this->admin_id = $row['admin_id'];
                return true;
            } else {
                // Удаление истекшей сессии
                $this->id = $session_id;
                $this->delete();
            }
        }

        return false;
    }

    /**
     * Удалить сессию
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

    /**
     * Удалить все сессии администратора
     */
    public function deleteByAdminId($admin_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE admin_id = :admin_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":admin_id", $admin_id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Удалить истекшие сессии
     */
    public function deleteExpired() {
        $query = "DELETE FROM " . $this->table_name . " WHERE expires_at < NOW()";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
    }
}
