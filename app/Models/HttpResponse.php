<?php
declare(strict_types=1);
namespace app\Models;

final class HttpResponse {
    /** @var string[] */
    public array $header_lines;

    private function __construct(
        public readonly string $protocol,
        public readonly int $status_code,
        public readonly string $response_phrase,
        string $header,
        public readonly string $body,
    ) {
        if (($status_code < 100) || (999 < $status_code)) {
            throw new \UnexpectedValueException(
                "'{$status_code}' is out of range for status_code");

        }
        $this->header_lines = explode("\r\n", $header);
    }

    public function to_string(): string {
        return implode("\r\n", [
            "{$this->protocol} {$this->status_code} {$this->response_phrase}",
            ...$this->header_lines,
            "",
            $this->body,
        ]);
    }

    public static function from_string(string $response): self {
        [$status_and_header, $body] = explode("\r\n\r\n", $response, 2);
        [$status_line, $header] = explode("\r\n", $status_and_header, 2);
        [$protocol, $status_code, $response_phrase] = explode(" ", $status_line, 3);

        if (strpos(strtoupper($protocol), "HTTP/1") !== 0) {
            throw new \UnexpectedValueException("'{$protocol}' is not supported protocol");
        }

        return new static(
            $protocol,
            (int)$status_code,
            $response_phrase,
            $header,
            $body ?: "",
        );
    }
}
