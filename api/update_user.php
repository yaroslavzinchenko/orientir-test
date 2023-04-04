<?php

require_once('includes/api_headers.php');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
    exit;
}

require_once('includes/connect_to_db.php');

// Требуется для кодирования веб-токена JSON.
require_once "config/core.php";
require_once "libs/php-jwt/src/BeforeValidException.php";
require_once "libs/php-jwt/src/ExpiredException.php";
require_once "libs/php-jwt/src/SignatureInvalidException.php";
require_once "libs/php-jwt/src/JWT.php";
require_once "libs/php-jwt/src/Key.php";
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Получаем данные от клиента.
$data = json_decode(file_get_contents("php://input"));
if (
    !isset($data->emailOld)
    || !isset($data->emailNew)
    || !isset($data->passwordOld)
    || !isset($data->passwordNew)
    || !isset($data->nameNew)
    || !isset($data->surnameNew)
    || !isset($data->secondnameNew)
    || !isset($data->postNew)
    || !isset($data->birthdayNew)
    || !isset($data->avatarNew)
    || !isset($data->jwt)
) {
    http_response_code(422);
    echo json_encode(array("message" => "Недостаточно параметров."));
    exit;
}

$user->email = $data->emailOld;
$email_exists = $user->emailExists();

if ($email_exists && password_verify($data->passwordOld, $user->password)) {
    $jwt = $data->jwt ?? '';
    if ($jwt) {
        try {
            $decoded = JWT::decode($jwt, new Key($key, 'HS256'));

            // Проверка на то, что пользователь использует jwt-ключ от своего емейла.
            if ($decoded->data->email != $user->email) {
                http_response_code(401);
                echo json_encode(array("message" => "Несоответствие jwt-ключа и адреса электронной почты."));
                exit;
            }

            $user->email = $data->emailNew;
            $user->password = $data->passwordNew;
            $user->id = $decoded->data->id;
            $user->name = $data->nameNew;
            $user->surname = $data->surnameNew;
            $user->secondname = $data->secondnameNew;
            $user->post = $data->postNew;
            $user->birthday = $data->birthdayNew;
            $user->avatar = $data->avatarNew;
            // Декодируем аватар для хранения в БД в сыром виде.
            $user->avatar = base64_decode($user->avatar);

            if ($user->update()) {
                // Необходимо заново сгенерировать JWT, потому что данные пользователя могут отличаться.
                $token = array(
                    "iss" => $iss,
                    "aud" => $aud,
                    "iat" => $iat,
                    "nbf" => $nbf,
                    "exp" => $exp,
                    "data" => array(
                        "id" => $user->id,
                        "email" => $user->email,
                    )
                );

                $jwt = JWT::encode($token, $key, 'HS256');

                http_response_code(200);
                echo json_encode(
                    array(
                        "message" => "Пользователь был обновлён",
                        "jwt" => $jwt,
                    )
                );
            } else {
                http_response_code(500);
                echo json_encode(array("message" => "Невозможно обновить пользователя"));
            }
        } catch (Exception $e) {
            // Если декодирование не удалось, значит, что JWT недействителен.
            http_response_code(401);
            echo json_encode(array(
                "message" => "Доступ запрещён",
                "error" => $e->getMessage()
            ));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Доступ запрещён"));
    }
} else {
    http_response_code(401);
    echo json_encode(array("message" => "Доступ запрещён"));
}