<?php
declare(strict_types=1);
namespace app\Models;

final class UnixDomainSocket extends AbstractSocket {
    function __construct(protected string $path) {
        parent::__construct();
    }

    protected function _create(): \Socket|false {
        return socket_create(AF_UNIX, SOCK_STREAM, 0);
    }

    protected function _connect(): bool {
        if ($this->sock === false) return false;

        return @socket_connect($this->sock, $this->path);
    }
}
