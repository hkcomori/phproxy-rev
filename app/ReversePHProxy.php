<?php
declare(strict_types=1);
namespace app;

final class ReversePHProxy {
    private readonly Models\Config $config;
    private readonly Models\Logger $logger;

    /**
     * @param array<string, string> $env    Environment variables
     */
    public function __construct(array $env) {
        $this->config = Models\Config::from_env($env);
        $this->logger = new Models\Logger($this->config->log_file, $this->config->log_level);
    }

    /**
     * @param array<string, string> $env    Environment variables
     * @param string $input                 Path to the input file
     */
    public function handle_request(array $env, string $input): void {
        try {
            $request = $this->get_backend_request($env, @file_get_contents($input) ?: '');
            $this->logger->debug($request->to_string());

            $response = $this->get_backend_response($request);
            $this->logger->debug($response->to_string());

            http_response_code($response->status_code);
            foreach ($response->header_lines as $value) {
                header($value);
            }
            echo $response->body;
        } catch (\Throwable $th) {
            $this->logger->catch($th);
        }
    }

    /**
     * @param array<string, string> $env    Environment variables
     * @param string $input                 Path to the input file
     */
    private function get_backend_request(array $env, string $input): Models\HttpRequest {
        return Models\HttpRequest::from_cgi($env, $input);
    }

    private function get_backend_response(Models\HttpRequest $request): Models\HttpResponse {
        $client = Models\HttpClient::create($this->config->backend_uri);
        try {
            return $client->send($request);
        } catch (Models\NotConnectableException $th) {
            if (empty($this->config->backend_cmd)) {
                throw new \RuntimeException(
                    "Unable to connect '{$this->config->backend_uri}'");
            }
            exec($this->config->backend_cmd);
            $client->wait_connectable($this->config->backend_timeout);
            return $client->send($request);
        }
    }
}
