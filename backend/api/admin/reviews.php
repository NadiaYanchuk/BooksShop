<?php
/**
 * API для управления отзывами (защищенный)
 */
require_once '../../config/cors.php';
require_once '../auth_middleware.php';
require_once '../../config/database.php';
require_once '../../models/Review.php';

// Проверка аутентификации
checkAuth();

$database = new Database();
$db = $database->getConnection();

$review = new Review($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Получить все отзывы с фильтрами
        $filters = [
            'is_approved' => $_GET['is_approved'] ?? '',
            'product_id' => $_GET['product_id'] ?? '',
            'rating' => $_GET['rating'] ?? ''
        ];

        $stmt = $review->readAll($filters);
        $reviews_arr = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $review_item = [
                "id" => $row['id'],
                "product_id" => $row['product_id'],
                "product_name" => $row['product_name'],
                "name" => $row['name'],
                "email" => $row['email'],
                "rating" => intval($row['rating']),
                "comment" => $row['comment'],
                "is_approved" => boolval($row['is_approved']),
                "created_at" => $row['created_at']
            ];

            array_push($reviews_arr, $review_item);
        }

        http_response_code(200);
        echo json_encode($reviews_arr);
        break;

    case 'PUT':
        // Обновить статус одобрения отзыва
        $data = json_decode(file_get_contents("php://input"));

        if (!empty($data->id) && isset($data->is_approved)) {
            $review->id = $data->id;
            $review->is_approved = $data->is_approved;

            if ($review->updateApproval()) {
                http_response_code(200);
                echo json_encode(["message" => "Статус отзыва обновлен"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при обновлении статуса"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Необходимо указать ID и статус"]);
        }
        break;

    case 'DELETE':
        // Удалить отзыв
        if (isset($_GET['id'])) {
            $review->id = $_GET['id'];
            
            if ($review->delete()) {
                http_response_code(200);
                echo json_encode(["message" => "Отзыв удален"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Ошибка при удалении отзыва"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Не указан ID отзыва"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["message" => "Метод не поддерживается"]);
        break;
}
