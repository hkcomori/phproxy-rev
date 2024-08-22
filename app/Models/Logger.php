<?php
declare(strict_types=1);
namespace app\Models;

final class Logger {
    /**
     * Target resource
     * @var resource
     */
    private $resource;
    private bool $debug_enable = false;
    private bool $info_enable = false;
    private bool $warning_enable = false;
    private bool $error_enable = false;

    public function __construct(string $uri, string $log_level) {
        switch (strtoupper($log_level)) {
            case 'DEBUG':
                $this->debug_enable = true;
            case 'INFO':
                $this->info_enable = true;
            case 'WARNING':
                $this->warning_enable = true;
            case 'ERROR':
                $this->error_enable = true;
                break;
            default:
                throw new \UnexpectedValueException('log_level: ' . $log_level);
        }

        $resource = fopen($uri, 'a');
        if (!is_resource($resource)) {
            throw new \RuntimeException('Cannot open ' . $uri);
        }
        $this->resource = $resource;
    }

    public function __destruct() {
        fclose($this->resource);
    }

    public function debug(string $message): void {
        if ($this->debug_enable && is_resource($this->resource)) {
            $this->output('[DEBUG] ', $message);
        }
    }

    public function info(string $message): void {
        if ($this->info_enable && is_resource($this->resource)) {
            $this->output('[INFO] ', $message);
        }
    }

    public function warning(string $message): void {
        if ($this->warning_enable && is_resource($this->resource)) {
            $this->output('[WARNING] ', $message);
        }
    }

    public function error(string $message): void {
        if ($this->error_enable && is_resource($this->resource)) {
            $this->output('[ERROR] ', $message);
        }
    }

    private function output(string $prefix, string $message): void {
        $lines = explode("\n", $message);
        foreach ($lines as $line) {
            fwrite($this->resource, $prefix . $line . "\n");
        }
    }
}
