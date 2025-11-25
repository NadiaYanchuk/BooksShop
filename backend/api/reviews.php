<?php
/**
 * API для работы с отзывами (публичный доступ)
 */
require_once '../config/cors.php';
require_once '../config/database.php';
require_once '../models/Review.php';

$database = new Database();
$db = $database->getConnection();

$review = new Review($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Получить одобренные отзывы (для главной страницы)
        if (isset($_GET['approved']) && $_GET['approved'] == '1') {
            $stmt = $review->readApproved();
            $reviews_arr = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $review_item = [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "rating" => intval($row['rating']),
                    "comment" => $row['comment'],
                    "created_at" => $row['created_at'],
                    "product_name" => $row['product_name']
                ];

                array_push($reviews_arr, $review_item);
            }

            http_response_code(200);
            echo json_encode($reviews_arr);
        }
        // Получить отзывы для продукта
        elseif (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            
            $stmt = $review->readByProduct($product_id);
            $reviews_arr = [];

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $review_item = [
                    "id" => $row['id'],
                    "name" => $row['name'],
                    "rating" => intval($row['rating']),
                    "comment" => $row['comment'],
                    "created_at" => $row['created_at'],
                    "product_name" => $row['product_name']
                ];

                array_push($reviews_arr, $review_item);
            }

            http_response_code(200);
            echo json_encode($reviews_arr);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан product_id или approved"]);
        }
        break;

    case 'POST':
        // Создать новый отзыв
        $data = json_decode(file_get_contents("php://input"));

        if (
            !empty($data->product_id) &&
            !empty($data->name) &&
            !empty($data->email) &&
            !empty($data->rating) &&
            !empty($data->comment)
        ) {
            $review->product_id = $data->product_id;
            $review->name = $data->name;
            $review->email = $data->email;
            $review->rating = $data->rating;
            $review->comment = $data->comment;

            if ($review->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Отзыв успешно добавлен. Он будет опубликован после модерации."]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при добавлении отзыва. Проверьте правильность данных."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Необходимо заполнить все поля"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}
