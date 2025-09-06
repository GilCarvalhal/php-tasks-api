<?php
declare(strict_types=1);

define('BASE_PATH', __DIR__);

/** Support */
require_once BASE_PATH . '/src/Support/Database.php';
require_once BASE_PATH . '/src/Support/Router.php';
require_once BASE_PATH . '/src/Support/Request.php';
require_once BASE_PATH . '/src/Support/Response.php';

/** Controller */
require_once BASE_PATH . '/src/Model/Task.php';
require_once BASE_PATH . '/src/Repository/TaskRepository.php';
require_once BASE_PATH . '/src/Repository/MysqlTaskRepository.php';
require_once BASE_PATH . '/src/Controller/TaskController.php';