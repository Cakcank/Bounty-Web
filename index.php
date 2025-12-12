<?php
$allowed_origins = [
    "http://localhost:5173",
    "https://bounty-web.netlify.app"
];

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

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
