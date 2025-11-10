<?php
/**
 * Gemini API Relay
 * ----------------
 * Промежуточный PHP-скрипт, который принимает запрос от galaglow.ru
 * и пересылает его на Google Gemini API, обходя блокировки.
 *
 * Размещается на Render.com (или другом зарубежном хостинге)
 * и вызывается из PHP-кода галереи, например:
 * $ch = curl_init("https://galaglow-relay.onrender.com/relay.php");
 */

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=utf-8");

// === 1. Чтение входящих данных ===
$input = json_decode(file_get_contents("php://input"), true);
if (empty($input['prompt'])) {
    echo json_encode(["error" => "No prompt provided"]);
    exit;
}

// === 2. Конфигурация ===
// !!! Никогда не храни ключ в открытом коде публично !!!
// На Render ключ хранится в Environment Variables → GEMINI_API_KEY
$apiKey = getenv("GEMINI_API_KEY");

if (!$apiKey) {
    echo json_encode(["error" => "Missing GEMINI_API_KEY in environment"]);
    exit;
}

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

// === 3. Формируем запрос ===
$payload = json_encode([
    "contents" => [
        [
            "role" => "user",
            "parts" => [
                ["text" => $input["prompt"]]
            ]
        ]
    ],
    "generationConfig" => [
        "temperature" => 0.2,
        "topP" => 0.8,
        "topK" => 10
    ]
]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
]);

$response = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// === 4. Возврат клиенту ===
if ($error) {
    echo json_encode(["error" => "cURL error: $error"]);
} elseif ($http_status != 200) {
    echo json_encode([
        "error" => "Gemini API HTTP $http_status",
        "response" => $response
    ]);
} else {
    echo $response;
}
