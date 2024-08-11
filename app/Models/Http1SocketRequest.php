<?php
declare(strict_types=1);
namespace app\Models;

final class Http1SocketRequest {
    /**
     * @param string $request_line
     * @param array<string, string> $headers
     * @param string $body
     */
    function __construct(private string $request_line, private array $headers, private string $body) {
    }

    public function to_string(): string {
        $lines = [$this->request_line];

        foreach ($this->headers as $key => $value) {
            $lines[] = rtrim("{$key}: {$value}");
        }

        $lines[] = "";
        $lines[] = $this->body;

        return implode("\r\n", $lines);
    }

    /**
     * @param array<string, string> $env    Server and runtime environment information
     * @param string $body                  HTTP request body
     */
    static public function from_cgi(array $env, string $body): self {
        $method = $env["REQUEST_METHOD"];
        $path_info = $env["PATH_INFO"] ?? $env["SCRIPT_URL"];
        $query_string = @$env["QUERY_STRING"] ? ("?" . $env["QUERY_STRING"]) : "";
        $protocol = $env["SERVER_PROTOCOL"];

        $request_line = "{$method} {$path_info}{$query_string} {$protocol}";
        $headers = [
            "Host" => $env["HTTP_HOST"] ?? "",
            "Content-Length" => $env["CONTENT_LENGTH"] ?? (string)strlen($body)
        ];

        $prefix = "HTTP_";
        $prefix_length = strlen($prefix);
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

            $prefix_removed = substr($key, $prefix_length);
            $header_key = static::convert_train_case_from($prefix_removed);
            $headers[$header_key] = $value;
        }

        return new static($request_line, $headers, $body);
    }

    /**
     * Convert style SCREAMING_SNAKE_CASE to Train-Case
     *
     * @param string $screaming_snake_case  SCREAMING_SNAKE_CASE
     * @return string Train-Case
     */
    static protected function convert_train_case_from(string $screaming_snake_case): string {
        $lowered_words = explode("_", strtolower($screaming_snake_case));
        $first_uppered_words = array_map("ucfirst", $lowered_words);

        return implode("-", $first_uppered_words);
    }
}
