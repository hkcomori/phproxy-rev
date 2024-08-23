<?php
declare(strict_types=1);
namespace app;

final class ReversePHProxy {
    /**
     * @param array<string, string> $env    Environment variables
     * @param string $input                 Path to the input file
     */
    public static function handle_request(array $env, string $input): void {
        $config = Models\Config::from_env($env);
        $logger = new Models\Logger($config->log_file, $config->log_level);

        $request = Models\HttpRequest::from_cgi(
            $env,
            @file_get_contents($input) ?: '',
        );

        $logger->debug($request->to_string());

        $client = Models\HttpClient::create($config->backend_uri);
        try {
            $response = $client->send($request);
        } catch (Models\NotConnectableException $th) {
            static::start_backend($config->backend_cmd);
            $client->wait_connectable($config->backend_timeout);
            $response = $client->send($request);
        }

        $logger->debug($response->to_string());

        http_response_code($response->status_code);
        foreach ($response->header_lines as $value) {
            header($value);
        }
        echo $response->body;
    }

    private static function start_backend(string $command): void {
        if (empty($command)) {
            throw new \RuntimeException("Cannot connect backend");
        }

        $proc = @proc_open($command, [], $pipes);
        if ($proc === false) {
            throw new \RuntimeException("Command not found: $command");
        }
    }
}
