<?php
/**
 * Скрипт импорта книг из Google Books API в базу данных
 * Run: php import_books.php
 */

require_once 'config/database.php';

// Подключаемся к БД
$database = new Database();
$conn = $database->getConnection();

if (!$conn) {
    die("Ошибка подключения к базе данных\n");
}

// Категории для загрузки книг
$categories = [
    'Fiction' => 5,
    'Mystery' => 5,
    'Romance' => 5,
    'SciFi' => 5,
    'Fantasy' => 5,
    'Thriller' => 5,
    'Horror' => 5,
    'Adventure' => 5,
    'History' => 5,
    'Biography' => 5
];

$totalImported = 0;

foreach ($categories as $category => $count) {
    echo "Загрузка категории: $category ($count книг)\n";
    
    // Запрос к Google Books API
    $url = "https://www.googleapis.com/books/v1/volumes?q=subject:{$category}&langRestrict=ru&maxResults={$count}&orderBy=relevance";
    
    $response = file_get_contents($url);
    
    if ($response === false) {
        echo "Ошибка при запросе к API\n";
        continue;
    }
    
    $data = json_decode($response, true);
    
    if (!isset($data['items']) || empty($data['items'])) {
        echo "Книги не найдены\n";
        continue;
    }
    
    $imported = 0;
    
    foreach ($data['items'] as $item) {
        try {
            $volumeInfo = $item['volumeInfo'] ?? [];
            $saleInfo = $item['saleInfo'] ?? [];
            
            // Извлекаем данные
            $googleBookId = $item['id'] ?? null;
            $title = $volumeInfo['title'] ?? 'Без названия';
            $authors = isset($volumeInfo['authors']) ? implode(', ', $volumeInfo['authors']) : 'Неизвестный автор';
            $description = $volumeInfo['description'] ?? 'Описание отсутствует';
            $publisher = $volumeInfo['publisher'] ?? 'Неизвестно';
            $publishedDate = $volumeInfo['publishedDate'] ?? null;
            $pageCount = $volumeInfo['pageCount'] ?? null;
            $imageUrl = $volumeInfo['imageLinks']['thumbnail'] ?? null;
            
            // ISBN
            $isbn = null;
            if (isset($volumeInfo['industryIdentifiers'])) {
                foreach ($volumeInfo['industryIdentifiers'] as $identifier) {
                    if ($identifier['type'] === 'ISBN_13' || $identifier['type'] === 'ISBN_10') {
                        $isbn = $identifier['identifier'];
                        break;
                    }
                }
            }
            
            // Цена (генерируем случайную, если нет в API)
            $price = 299.00;
            if (isset($saleInfo['listPrice']['amount'])) {
                $price = $saleInfo['listPrice']['amount'];
            } else {
                $price = rand(199, 999);
            }
            
            // Проверяем, не существует ли уже эта книга
            if ($googleBookId) {
                $checkStmt = $conn->prepare("SELECT id FROM products WHERE google_book_id = ?");
                $checkStmt->execute([$googleBookId]);
                if ($checkStmt->fetch()) {
                    echo "Пропускаем (уже существует): $title\n";
                    continue;
                }
            }
            
            // Вставляем книгу в БД
            $stmt = $conn->prepare("
                INSERT INTO products 
                (name, description, authors, publisher, published_date, page_count, isbn, google_book_id, 
                 price, category, image_url, stock, is_active) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stock = rand(5, 50); // Случайное количество в наличии
            $isActive = 1;
            
            $stmt->execute([
                $title,
                $description,
                $authors,
                $publisher,
                $publishedDate,
                $pageCount,
                $isbn,
                $googleBookId,
                $price,
                $category,
                $imageUrl,
                $stock,
                $isActive
            ]);
            
            $imported++;
            $totalImported++;
            
            echo "Добавлено: $title (Автор: $authors)\n";
            
        } catch (Exception $e) {
            echo "Ошибка при добавлении книги: " . $e->getMessage() . "\n";
        }
    }
    echo "Импортировано из категории $category: $imported книг\n\n";
    
    sleep(1);
}
echo "Всего импортировано книг: $totalImported\n";

?>
