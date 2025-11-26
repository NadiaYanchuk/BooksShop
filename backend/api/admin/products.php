<?php
/**
 * API для управления продуктами (защищенный)
 */
require_once '../../config/cors.php';
require_once '../auth_middleware.php';
require_once '../../config/database.php';
require_once '../../models/Product.php';

// Проверка аутентификации
checkAuth();

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Получить все продукты с фильтрами
        $filters = [
            'category' => $_GET['category'] ?? '',
            'is_active' => $_GET['is_active'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Получить список категорий
        if (isset($_GET['categories'])) {
            $stmt = $product->getCategories();
            $categories = [];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($categories, $row['category']);
            }
            
            http_response_code(200);
            echo json_encode($categories);
            break;
        }

        $stmt = $product->readAllAdmin($filters);
        $products_arr = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $product_item = [
                "id" => $row['id'],
                "name" => $row['name'],
                "description" => $row['description'],
                "authors" => $row['authors'] ?? '',
                "price" => floatval($row['price']),
                "category" => $row['category'],
                "image_url" => $row['image_url'],
                "stock" => intval($row['stock']),
                "is_active" => boolval($row['is_active']),
                "created_at" => $row['created_at'],
                "updated_at" => $row['updated_at']
            ];

            array_push($products_arr, $product_item);
        }

        http_response_code(200);
        echo json_encode($products_arr);
        break;

    case 'POST':
        // Создать новый продукт
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->name) &&
            !empty($data->price) &&
            !empty($data->category)
        ) {
            $product->name = $data->name;
            $product->description = $data->description ?? '';
            $product->price = $data->price;
            $product->category = $data->category;
            $product->image_url = !empty($data->image_url) ? $data->image_url : 'assets/no-image.png';
            $product->stock = $data->stock ?? 0;
            $product->is_active = $data->is_active ?? true;

            if ($product->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Продукт успешно создан"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при создании продукта"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Необходимо заполнить обязательные поля"]);
        }
        break;

    case 'PUT':
        // Обновить продукт
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id)) {
            $product->id = $data->id;
            $product->name = $data->name;
            $product->description = $data->description ?? '';
            $product->price = $data->price;
            $product->category = $data->category;
            $product->image_url = !empty($data->image_url) ? $data->image_url : 'assets/no-image.png';
            $product->stock = $data->stock ?? 0;
            $product->is_active = $data->is_active ?? true;

            if ($product->update()) {
                http_response_code(200);
                echo json_encode(["message" => "Продукт успешно обновлен"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при обновлении продукта"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан ID продукта"]);
        }
        break;

    case 'DELETE':
        // Удалить продукт
        if (isset($_GET['id'])) {
            $product->id = $_GET['id'];
            
            if ($product->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Продукт удален"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при удалении продукта"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан ID продукта"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}
