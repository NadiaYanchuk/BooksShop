<?php
/**
 * Модель продукта
 */
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $name;
    public $description;
    public $authors;
    public $publisher;
    public $published_date;
    public $page_count;
    public $isbn;
    public $google_book_id;
    public $price;
    public $category;
    public $image_url;
    public $stock;
    public $is_active;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Получить все активные продукты (для публичного доступа)
     */
    public function readAll() {
        $query = "SELECT id, name, description, authors, publisher, published_date, 
                         page_count, isbn, google_book_id, price, category, image_url, stock 
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Получить все продукты (для администратора)
     */
    public function readAllAdmin($filters = []) {
        $query = "SELECT id, name, description, authors, price, category, image_url, stock, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " WHERE 1=1";

        // Применение фильтров
        if (!empty($filters['category'])) {
            $query .= " AND category = :category";
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query .= " AND is_active = :is_active";
        }
        if (!empty($filters['search'])) {
            $query .= " AND (name LIKE :search1 OR description LIKE :search2 OR authors LIKE :search3 OR isbn LIKE :search4)";
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Привязка параметров фильтров
        if (!empty($filters['category'])) {
            $stmt->bindValue(":category", $filters['category']);
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $stmt->bindValue(":is_active", $filters['is_active']);
        }
        if (!empty($filters['search'])) {
            $search = "%" . $filters['search'] . "%";
            $stmt->bindValue(":search1", $search);
            $stmt->bindValue(":search2", $search);
            $stmt->bindValue(":search3", $search);
            $stmt->bindValue(":search4", $search);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Поиск продуктов (для публичного доступа)
     */
    public function search($keyword, $category = '') {
        $query = "SELECT id, name, description, authors, publisher, published_date, 
                         page_count, isbn, google_book_id, price, category, image_url, stock 
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1 
                  AND (name LIKE :keyword1 OR description LIKE :keyword2 OR authors LIKE :keyword3)";

        if (!empty($category)) {
            $query .= " AND category = :category";
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        $keyword = "%" . htmlspecialchars(strip_tags($keyword)) . "%";
        $stmt->bindValue(":keyword1", $keyword);
        $stmt->bindValue(":keyword2", $keyword);
        $stmt->bindValue(":keyword3", $keyword);

        if (!empty($category)) {
            $category = htmlspecialchars(strip_tags($category));
            $stmt->bindValue(":category", $category);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Получить продукты с расширенными фильтрами (для публичного доступа)
     */
    public function filter($filters = []) {
        $query = "SELECT id, name, description, authors, publisher, published_date, 
                         page_count, isbn, google_book_id, price, category, image_url, stock 
                  FROM " . $this->table_name . " 
                  WHERE is_active = 1";

        // Поиск по ключевому слову
        if (!empty($filters['keyword'])) {
            $query .= " AND (name LIKE :keyword1 OR description LIKE :keyword2 OR authors LIKE :keyword3)";
        }

        // Фильтр по категории
        if (!empty($filters['category'])) {
            $query .= " AND category = :category";
        }

        // Фильтр по минимальной цене
        if (isset($filters['minPrice']) && $filters['minPrice'] !== '') {
            $query .= " AND price >= :minPrice";
        }

        // Фильтр по максимальной цене
        if (isset($filters['maxPrice']) && $filters['maxPrice'] !== '') {
            $query .= " AND price <= :maxPrice";
        }

        $query .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Привязка параметров
        if (!empty($filters['keyword'])) {
            $keyword = "%" . htmlspecialchars(strip_tags($filters['keyword'])) . "%";
            $stmt->bindValue(":keyword1", $keyword);
            $stmt->bindValue(":keyword2", $keyword);
            $stmt->bindValue(":keyword3", $keyword);
        }

        if (!empty($filters['category'])) {
            $category = htmlspecialchars(strip_tags($filters['category']));
            $stmt->bindValue(":category", $category);
        }

        if (isset($filters['minPrice']) && $filters['minPrice'] !== '') {
            $minPrice = floatval($filters['minPrice']);
            $stmt->bindValue(":minPrice", $minPrice, PDO::PARAM_STR);
        }

        if (isset($filters['maxPrice']) && $filters['maxPrice'] !== '') {
            $maxPrice = floatval($filters['maxPrice']);
            $stmt->bindValue(":maxPrice", $maxPrice, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * Получить продукт по ID
     */
    public function readOne() {
        $query = "SELECT id, name, description, price, category, image_url, stock, is_active, created_at, updated_at 
                  FROM " . $this->table_name . " 
                  WHERE id = :id 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $this->name = $row['name'];
            $this->description = $row['description'];
            $this->price = $row['price'];
            $this->category = $row['category'];
            $this->image_url = $row['image_url'];
            $this->stock = $row['stock'];
            $this->is_active = $row['is_active'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }

        return false;
    }

    /**
     * Создать продукт
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, description, price, category, image_url, stock, is_active) 
                  VALUES (:name, :description, :price, :category, :image_url, :stock, :is_active)";

        $stmt = $this->conn->prepare($query);

        // Очистка данных
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        // Для URL используем trim вместо htmlspecialchars, чтобы не повредить ссылку
        $this->image_url = trim($this->image_url);
        
        // Преобразование boolean в integer для MySQL
        $is_active_int = $this->is_active ? 1 : 0;

        // Привязка параметров
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":is_active", $is_active_int, PDO::PARAM_INT);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Обновить продукт
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name, 
                      description = :description, 
                      price = :price, 
                      category = :category, 
                      image_url = :image_url, 
                      stock = :stock, 
                      is_active = :is_active 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Очистка данных
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->category = htmlspecialchars(strip_tags($this->category));
        // Для URL используем trim вместо htmlspecialchars, чтобы не повредить ссылку
        $this->image_url = trim($this->image_url);
        
        // Преобразование boolean в integer для MySQL
        $is_active_int = $this->is_active ? 1 : 0;

        // Привязка параметров
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":is_active", $is_active_int, PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * Удалить продукт
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
     * Получить все категории
     */
    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table_name . " ORDER BY category";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
