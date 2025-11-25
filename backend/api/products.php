<?php
/**
 * API для работы с продуктами (публичный доступ)
 */
require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Поиск продуктов
    if (isset($_GET['search'])) {
        $keyword = $_GET['search'];
        $category = $_GET['category'] ?? '';
        
        $stmt = $product->search($keyword, $category);
        $products_arr = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product_item = [
                "id" => $row['id'],
                "name" => $row['name'],
                "description" => $row['description'],
                "authors" => $row['authors'],
                "publisher" => $row['publisher'],
                "published_date" => $row['published_date'],
                "page_count" => $row['page_count'] ? intval($row['page_count']) : null,
                "isbn" => $row['isbn'],
                "google_book_id" => $row['google_book_id'],
                "price" => floatval($row['price']),
                "category" => $row['category'],
                "image_url" => $row['image_url'],
                "stock" => intval($row['stock'])
            ];

            array_push($products_arr, $product_item);
        }

        http_response_code(200);
        echo json_encode($products_arr);
    }
    // Получить один продукт
    else if (isset($_GET['id'])) {
        $product->id = $_GET['id'];
        
        if ($product->readOne()) {
            $product_arr = [
                "id" => $product->id,
                "name" => $product->name,
                "description" => $product->description,
                "authors" => $product->authors,
                "publisher" => $product->publisher,
                "published_date" => $product->published_date,
                "page_count" => $product->page_count ? intval($product->page_count) : null,
                "isbn" => $product->isbn,
                "google_book_id" => $product->google_book_id,
                "price" => floatval($product->price),
                "category" => $product->category,
                "image_url" => $product->image_url,
                "stock" => intval($product->stock)
            ];

            http_response_code(200);
            echo json_encode($product_arr);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Продукт не найден"]);
        }
    }
    // Получить все продукты
    else {
        $stmt = $product->readAll();
        $products_arr = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product_item = [
                "id" => $row['id'],
                "name" => $row['name'],
                "description" => $row['description'],
                "authors" => $row['authors'],
                "publisher" => $row['publisher'],
                "published_date" => $row['published_date'],
                "page_count" => $row['page_count'] ? intval($row['page_count']) : null,
                "isbn" => $row['isbn'],
                "google_book_id" => $row['google_book_id'],
                "price" => floatval($row['price']),
                "category" => $row['category'],
                "image_url" => $row['image_url'],
                "stock" => intval($row['stock'])
            ];

            array_push($products_arr, $product_item);
        }

        http_response_code(200);
        echo json_encode($products_arr);
    }
} else {
    http_response_code(405);
    echo json_encode(["message" => "Метод не поддерживается"]);
}
