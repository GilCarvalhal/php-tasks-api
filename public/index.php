<?php
declare(strict_types=1);

use App\Support\Database;
use App\Support\Router;
use App\Support\Response;
use App\Support\Request;

require_once __DIR__ . '/../bootstrap.php';

/** CORS */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS')
{
    http_response_code(204);

    exit;
}

$router = new Router();

$router->get('/health', function (Request $r) 
{
    if($r->queryParam('deep') !== null)
    {
        try
        {
            Database::pdo()->query('SELECT 1');

            return Response::json(['status' => 'ok', 'db' => 'ok']);
        }
        catch(\Throwable)
        {
            return Response::json(['status' => 'degraded', 'db' => 'fail'], 503);
        }
    }

    Response::json(['status' => 'ok']);
});

$router->dispatch(Request::fromGlobals());