<?php
declare(strict_types=1);
namespace app\Models;

abstract class AbstractSocket {
    protected \Socket|false $sock;

    abstract protected function _connect(): bool;
    abstract protected function _create(): \Socket|false;

    function __construct() {
        $this->sock = false;
    }

    function __destruct() {
        if ($this->sock !== false) {
            socket_close($this->sock);
        }
    }

    /**
     * Connect to socket and execute command if socket is not listening
     *
     * @param string $command   Command to start socket listening
     * @param int $timeout_sec  Timeout to wait for start socket listening
     */
    public function connect(string $command, int $timeout_sec): void {
        $this->sock = $this->_create();
        if ($this->sock === false) {
            throw new \RuntimeException("Create socket failed");
        }

        if ($this->_connect() === false) {
            if (empty($command)) {
                throw new \RuntimeException("Socket connect failed");
            }

            $time_limit = time() + $timeout_sec;
            $proc = @proc_open($command, [], $pipes);
            if ($proc === false) {
                throw new \RuntimeException("proc_open failed: $command");
            }

            // @phpstan-ignore identical.alwaysTrue (Because timeout exists)
            while ($this->_connect() === false) {
                sleep(1);
                if (time() > $time_limit) {
                    throw new \RuntimeException("Recovery socket timeout");
                }
            }
        }
    }

    /**
     * Send request to socket
     *
     * @param string $request   Request to send
     * @param int $size         Chunk size when received
     * @return string   Response received
     */
    public function send(string $request, int $size = 4096,): string {
        if ($this->sock === false) return "";

        socket_write($this->sock, $request, strlen($request));

        $r = [$this->sock];
        $w = null;
        $e = null;
        $response = "";
        do {
            $num_changed_sockets = socket_select($r, $w, $e, 0);
            if ($num_changed_sockets === false || $num_changed_sockets <= 0) {
                break;
            }
            $chunk = socket_read($this->sock, $size);
            if ($chunk === false) {
                break;
            }

            $response .= $chunk;
        } while (strlen($chunk) === 0);

        return $response;
    }
}
