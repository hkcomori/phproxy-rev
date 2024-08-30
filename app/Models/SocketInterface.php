<?php
declare(strict_types=1);
namespace app\Models;

interface SocketInterface {
    public function connect(): bool;
}
