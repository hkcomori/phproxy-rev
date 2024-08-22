<?php
declare(strict_types=1);
namespace app\Models;

final class HttpClient {
    /**
     * @param array<int, mixed> $opts   Associative array of Curl options
     */
    private function __construct(protected array $opts) {
    }

    /**
     * Connect to socket and execute command if socket is not listening
     *
     * @param HttpRequest $request   HTTP request
     * @return HttpResponse          HTTP response
     */
    public function send(HttpRequest $request): HttpResponse {
        $host = $request->headers['Host'] ?? 'localhost';
        $ch = curl_init($host . $request->path);
        if ($ch === false) {
            throw new \RuntimeException('curl_init failed');
        }

        try {
            foreach ($this->opts as $option => $value) {
                curl_setopt($ch, $option, $value);
            }

            $method = strtoupper($request->method);
            switch ($method) {
                case 'GET':
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                    break;
                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
                    break;
                default:
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                    break;
            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $request->header_lines());
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);

            assert($response !== true);
            if ($response === false) {
                throw new NotConnectableException(curl_error($ch), curl_errno($ch));
            }
        } finally {
            curl_close($ch);
        }

        return HttpResponse::from_string($response);
    }

    public function wait_connectable(int $timeout_sec): void {
        $time_limit = time() + $timeout_sec;
        $ch = curl_init();
        if ($ch === false) {
            throw new \RuntimeException('curl_init failed');
        }

        try {
            curl_setopt($ch, CURLOPT_CONNECT_ONLY, true);
            while (curl_exec($ch) === false) {
                sleep(1);
                if (time() > $time_limit) {
                    throw new \RuntimeException("Cannot connect backend");
                }
            }
        } finally {
            curl_close($ch);
        }
    }

    public static function create(string $uri): Self {
        $opts = [];
        $parsed_url = parse_url($uri);
        if (($parsed_url === false) || (array_key_exists("scheme", $parsed_url) === false)) {
            throw new \UnexpectedValueException("Invalid URL: " . $uri);
        }

        switch ($parsed_url["scheme"]) {
            case "unix":
                if (array_key_exists("path", $parsed_url) === false) {
                    throw new \UnexpectedValueException("Invalid URL: " . $uri);
                }
                $opts[CURLOPT_UNIX_SOCKET_PATH] = $parsed_url["path"];
                break;
            default:
                throw new \UnexpectedValueException("Unsupported scheme: " . $uri);
        }
        return new static($opts);
    }
}
