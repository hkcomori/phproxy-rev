<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

app\ReversePHProxy::handle_request($_SERVER, "php://input");
