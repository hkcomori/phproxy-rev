<?php
declare(strict_types=1);
namespace app;

final class ReversePHProxy {
    /**
     * @param array<string, string> $env    Environment variables
     * @param string $input                 Path to the input file
     */
    static public function handle_request(array $env, string $input): void {
        $display_http_enabled = false;
        if (array_key_exists("REVERSE_PHPROXY_DEBUG", $_SERVER) === true) {
            switch (strtolower($_SERVER['REVERSE_PHPROXY_DEBUG'])) {
                case 'display-http':
                    $display_http_enabled = true;
                    break;
                case 'display-env':
                default:
                    header("Content-Type: application/json; charset=utf-8");
                    echo json_encode($_SERVER);
                    return;
            }
        }

        $body = @file_get_contents($input) ?: "";
        $request = Models\Http1SocketRequest::from_cgi($env, $body)->to_string();
        unset($body);

        if ($display_http_enabled === true) {
            echo $request;
        }

        $sock = (new Models\SocketFactory)->create($env["REVERSE_PHPROXY_BACKEND"]);
        $sock->connect(
            $env["REVERSE_PHPROXY_START_BACKEND"] ?? "",
            (int)($env["REVERSE_PHPROXY_START_BACKEND_TIMEOUT"] ?? "180"),
        );
        $response = $sock->send($request);

        if ($display_http_enabled === true) {
            echo "---\r\n";
            echo $response;
            return;
        }

        $parsed_response = Models\Http1SocketResponse::from_string($response);
        unset($sock, $request, $response);

        http_response_code($parsed_response->status_code);
        foreach ($parsed_response->header_lines as $value) {
            header($value);
        }
        echo $parsed_response->body;
    }
}
