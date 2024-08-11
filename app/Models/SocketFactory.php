<?php
declare(strict_types=1);
namespace app\Models;

final class SocketFactory {
    public function create(string $uri): AbstractSocket {
        $parsed_url = parse_url($uri);
        if (($parsed_url === false) || (array_key_exists("scheme", $parsed_url) === false)) {
            throw new \UnexpectedValueException("Invalid URL: " . $uri);
        }

        switch ($parsed_url["scheme"]) {
            case "unix":
                if (array_key_exists("path", $parsed_url) === false) {
                    throw new \UnexpectedValueException("Invalid URL: " . $uri);
                }
                return new UnixDomainSocket($parsed_url["path"]);
            default:
                throw new \UnexpectedValueException("Unsupported scheme: " . $uri);
        }
    }
}
