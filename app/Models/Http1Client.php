<?php
declare(strict_types=1);
namespace app\Models;

final class Http1Client {
    /**
     * @param array<int, mixed> $opts   Associative array of Curl options
     */
    function __construct(protected array $opts) {
    }

    /**
     * Connect to socket and execute command if socket is not listening
     *
     * @param Http1SocketRequest $request   HTTP request
     * @return Http1SocketResponse          HTTP response
     */
    public function send(Http1SocketRequest $request): Http1SocketResponse|false {
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
        } finally {
            curl_close($ch);
        }

        if (is_string($response) === false) {
            return false;
        }
        return Http1SocketResponse::from_string($response);
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
