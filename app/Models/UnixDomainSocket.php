<?php
declare(strict_types=1);
namespace app\Models;

final class UnixDomainSocket implements SocketInterface {
    private \Socket $socket;

    public function __construct(private string $address) {
        $socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        if (!($socket instanceof \Socket)) {
            $code = socket_last_error();
            $msg = socket_strerror($code);
            throw new \RuntimeException("socket_create(): unable to create [{$code}]: {$msg}");
        }
        $this->socket = $socket;
    }

    public function __destruct() {
        socket_close($this->socket);
    }

    public function connect(): bool {
        return @socket_connect($this->socket, $this->address);
    }
}
