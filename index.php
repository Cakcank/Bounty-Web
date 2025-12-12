<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Untuk OPTIONS (preflight) biar tidak error
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// index.php
require_once "db.php";
echo json_encode([
    "status" => "bounty-api active",
    "endpoints" => [
        "auth/login.php",
        "auth/logout.php",
        "missing/get.php",
        "ads/get.php",
        "chat/list.php"
    ]
]);