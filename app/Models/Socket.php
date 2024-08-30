<?php
declare(strict_types=1);
namespace app\Models;

class Socket {
    public function __construct(
        private int $domain,
        private int $type,
        private int $protocol,
        private string $address,
        private int|null $port = null,
    ) {
    }

    private function socket_create(): \Socket {
        $socket = socket_create($this->domain, $this->type, $this->protocol);
        if (!($socket instanceof \Socket)) {
            throw new \RuntimeException("socket_create({$this->domain}, {$this->type}, {$this->protocol}) failed");
        }
        return $socket;
    }

    public function is_connectable(): bool {
        $socket = $this->socket_create();
        try {
            return @socket_connect($socket, $this->address, $this->port);
        } finally {
            socket_close($socket);
        }
    }
}
