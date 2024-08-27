<?php
declare(strict_types=1);
namespace app\Models;

final class HttpRequest {
    /**
     * @param string $method
     * @param string $path
     * @param string $protocol
     * @param array<string, string> $headers
     * @param string $body
     */
    private function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly string $protocol,
        public readonly array $headers,
        public readonly string $body,
    ) {
        switch ($method) {
            case 'GET':
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
            case 'HEAD':
            case 'OPTIONS':
            case 'CONNECT':
            case 'TRACE':
                break;
            default:
                throw new \UnexpectedValueException("'{$method}' is unknown method");
        }

        if (strpos($protocol, 'HTTP') !== 0) {
            throw new \UnexpectedValueException("'{$protocol}' is not supported protocol");
        }
    }

    public function to_string(): string {
        $lines = [
            $this->request_line(),
            ...$this->header_lines(),
            "",
            $this->body,
        ];

        return implode("\r\n", $lines);
    }

    /**
     * @return string       Request line
     */
    public function request_line(): string {
        return "{$this->method} {$this->path} {$this->protocol}";
    }

    /**
     * @return string[]     Header lines
     */
    public function header_lines(): array {
        $lines = [];

        foreach ($this->headers as $key => $value) {
            $lines[] = rtrim("{$key}: {$value}");
        }

        return $lines;
    }

    /**
     * @param array<string, string> $env    Server and runtime environment information
     * @param string $body                  HTTP request body
     */
    public static function from_cgi(array $env, string $body): self {
        $method = strtoupper($env["REQUEST_METHOD"]);
        $path_info = $env["PATH_INFO"] ?? $env["SCRIPT_URL"];
        $query_string = @$env["QUERY_STRING"] ? ("?" . $env["QUERY_STRING"]) : "";
        $protocol = $env["SERVER_PROTOCOL"];

        $path = "{$path_info}{$query_string}";
        $headers = [
            "Host" => $env["HTTP_HOST"] ?? "",
            "Content-Length" => $env["CONTENT_LENGTH"] ?? (string)strlen($body)
        ];

        foreach ($env as $key => $value) {
            switch ($key) {
                case 'HTTP_HOST':
                    continue 2;
                case 'REMOTE_ADDR':
                    $headers["X-Real-IP"] = $value;
                    if (array_key_exists("HTTP_X_FORWARDED_FOR", $env) === false) {
                        $headers["X-Forwarded-For"] = $value;
                    } else {
                        $headers["X-Forwarded-For"] = $env['HTTP_X_FORWARDED_FOR'] . ", " . $value;
                    }
                    continue 2;
                case 'SERVER_NAME':
                    $headers["X-Forwarded-Host"] = $value;
                    continue 2;
                case 'REQUEST_SCHEME':
                    $headers["X-Forwarded-Proto"] = $value;
                    continue 2;
                default:
                    if (strpos($key, 'HTTP_') !== 0) continue 2;
            }

            $prefix_removed = substr($key, strlen('HTTP_'));
            $header_key = static::convert_train_case_from($prefix_removed);
            $headers[$header_key] = $value;
        }

        return new static($method, $path, $protocol, $headers, $body);
    }

    /**
     * Convert style SCREAMING_SNAKE_CASE to Train-Case
     *
     * @param string $screaming_snake_case  SCREAMING_SNAKE_CASE
     * @return string Train-Case
     */
    protected static function convert_train_case_from(string $screaming_snake_case): string {
        $lowered_words = explode("_", strtolower($screaming_snake_case));
        $first_uppered_words = array_map("ucfirst", $lowered_words);

        return implode("-", $first_uppered_words);
    }
}
