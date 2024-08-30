<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

$server = new app\ReversePHProxy($_SERVER);
$server->handle_request($_SERVER, "php://input");
