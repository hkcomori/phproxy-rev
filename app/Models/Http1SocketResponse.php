<?php
declare(strict_types=1);
namespace app\Models;

final class Http1SocketResponse {
    /** @var string[] */
    public array $header_lines;

    function __construct(public int $status_code, string $header, public string $body) {
        if (($status_code < 100) || (999 < $status_code)) {
            throw new \UnexpectedValueException("status_code: ".$status_code);

        }
        $this->header_lines = explode("\r\n", $header);
    }

    static public function from_string(string $response): self {
        [$status_and_header, $body] = explode("\r\n\r\n", $response, 2);
        [$status_line, $header] = explode("\r\n", $status_and_header, 2);
        [$protocol, $status_code, $response_phrase] = explode(" ", $status_line, 3);

        if (strpos(strtoupper($protocol), "HTTP/1") !== 0) {
            throw new \UnexpectedValueException("Protocol: ".$protocol);
        }

        return new static((int)$status_code, $header, $body ?: "");
    }
}