<?php
declare(strict_types=1);
namespace app\Models;

final class Config {
    private function __construct(
        public readonly string $backend_uri,
        public readonly string $backend_cmd,
        public readonly int $backend_timeout,
        public readonly string $log_level,
        public readonly string $log_file,
    ) {
    }

    /**
     * Load config from environment variables
     *
     * @param array<string, string> $env    Environment variables
     */
    public static function from_env(array $env): Self {
        return new static(
            $env["REVERSE_PHPROXY_BACKEND_URI"],
            $env['REVERSE_PHPROXY_BACKEND_CMD'] ?? '',
            (int)($env['REVERSE_PHPROXY_BACKEND_TIMEOUT'] ?? '180'),
            $env['REVERSE_PHPROXY_LOG_LEVEL'] ?? 'WARNING',
            $env['REVERSE_PHPROXY_LOG_FILE'] ?? 'php://stdout',
        );
    }
}
