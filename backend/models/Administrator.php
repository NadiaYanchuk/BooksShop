<?php
/**
 * Модель администратора
 */
class Administrator {
    private $conn;
    private $table_name = "administrators";

    public $id;
    public $username;
    public $email;
    public $password_hash;
    public $salt;
    public $created_at;
    public $last_login;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Создание нового администратора
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (username, email, password_hash, salt) 
                  VALUES (:username, :email, :password_hash, :salt)";

        $stmt = $this->conn->prepare($query);

        // Очистка данных
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Генерация соли и хэширование пароля
        $this->salt = bin2hex(random_bytes(32));
        $this->password_hash = hash('sha256', $this->password_hash . $this->salt);

        // Привязка параметров
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":salt", $this->salt);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Аутентификация администратора
     */
    public function authenticate($username, $password) {
        $query = "SELECT id, username, email, password_hash, salt 
                  FROM " . $this->table_name . " 
                  WHERE username = :username 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $username = htmlspecialchars(strip_tags($username));
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Проверка пароля
            $password_hash = hash('sha256', $password . $row['salt']);
            
            if($password_hash === $row['password_hash']) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->email = $row['email'];
                
                // Обновление времени последнего входа
                $this->updateLastLogin();
                
                return true;
            }
        }

        return false;
    }

    /**
     * Обновление времени последнего входа
     */
    private function updateLastLogin() {
        $query = "UPDATE " . $this->table_name . " 
                  SET last_login = CURRENT_TIMESTAMP 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
    }

    /**
     * Получить всех администраторов
     */
    public function readAll() {
        $query = "SELECT id, username, email, created_at, last_login 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Проверка существования пользователя
     */
    public function exists() {
        $query = "SELECT id FROM " . $this->table_name . " 
                  WHERE username = :username OR email = :email 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    /**
     * Удаление администратора
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
