<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Untuk OPTIONS (preflight) biar tidak error
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// auth.php
require_once __DIR__ . "/db.php";

/*
|--------------------------------------------------------------------------
| GET BEARER TOKEN
|--------------------------------------------------------------------------
| Mengambil token dari header Authorization.
| Format header harus: Authorization: Bearer xxxxx
*/
function getBearerToken() {
    $headers = null;

    // 1. PHP Native
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    }

    // 2. Apache / Nginx
    else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    }

    // 3. Fallback untuk beberapa konfigurasi server
    elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    if (!$headers) return null;

    // Ambil token di format "Bearer xxxxxxxxx"
    if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
        return $matches[1];
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| GET USER BY TOKEN
|--------------------------------------------------------------------------
| Mengambil data user berdasarkan token.
*/
function getUserByToken($token) {
    global $conn;
    if (!$token) return null;

    $stmt = $conn->prepare("SELECT id, username, role FROM users WHERE token = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    return $user ?: null;
}

/*
|--------------------------------------------------------------------------
| REQUIRE AUTH (Middleware)
|--------------------------------------------------------------------------
| Fungsi ini untuk file API yang butuh login.
| Contoh: $user = requireAuth();
*/
function requireAuth() {
    $token = getBearerToken();
    $user = getUserByToken($token);

    if (!$user) {
        http_response_code(401);
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    return $user;
}

/*
|--------------------------------------------------------------------------
| Jika auth.php diakses langsung
|--------------------------------------------------------------------------
*/
if (basename(__FILE__) === basename($_SERVER["SCRIPT_FILENAME"])) {
    echo json_encode(["auth" => "OK"]);
    exit;
}
