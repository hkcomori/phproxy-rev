<?php
declare(strict_types=1);
namespace app\Models;

final class Config {
    private function __construct(
        public readonly string $backend_uri,
        public readonly string $start_backend_cmd,
        public readonly int $start_backend_timeout,
        public readonly string $log_level,
    ) {
    }

    /**
     * Load config from environment variables
     *
     * @param array<string, string> $env    Environment variables
     */
    public static function from_env(array $env): Self {
        return new static(
            $env["REVERSE_PHPROXY_BACKEND"],
            $env['REVERSE_PHPROXY_START_BACKEND'] ?? '',
            (int)($env['REVERSE_PHPROXY_START_BACKEND_TIMEOUT'] ?? '180'),
            $env['REVERSE_PHPROXY_LOG_LEVEL'] ?? 'WARNING',
        );
    }
}
