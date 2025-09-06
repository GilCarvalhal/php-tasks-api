<?php
declare(strict_types=1);

use App\Support\Database;
use App\Support\Router;
use App\Support\Response;
use App\Support\Request;

use App\Repository\MysqlTaskRepository;
use App\Controller\TaskController;

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

$repo = new MysqlTaskRepository(Database::pdo());
$tasks = new TaskController($repo);

$router->get   ('/tasks',       [$tasks, 'index']);
$router->get   ('/tasks/{id}',  [$tasks, 'show']);
$router->post  ('/tasks',       [$tasks, 'store']);
$router->put   ('/tasks/{id}',  [$tasks, 'update']);
$router->delete('/tasks/{id}',  [$tasks, 'destroy']);

$router->options('/tasks',      fn() => Response::noContent());
$router->options('/tasks/{id}', fn() => Response::noContent());

$router->dispatch(Request::fromGlobals());