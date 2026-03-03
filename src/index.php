<?php

declare(strict_types=1);

require_once __DIR__ . "/vendor/autoload.php";

use App\Controllers\RankingController;
use App\Repositories\MovementRepository;
use App\Services\RankingService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

header("Content-Type: application/json; charset=utf-8");

$method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$uri = rtrim($uri, "/");

// GET /rankings/{identifier}
if ($method === "GET" && preg_match('#^/rankings/(.+)$#', $uri, $matches)) {
    $identifier = urldecode($matches[1]);

    $controller = new RankingController(
        new RankingService(new MovementRepository()),
    );

    $controller->ranking($identifier);
    exit();
}

http_response_code(404);
echo json_encode(["error" => "Route not found."], JSON_PRETTY_PRINT);
