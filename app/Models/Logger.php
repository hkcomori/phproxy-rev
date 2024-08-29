<?php
declare(strict_types=1);
namespace app\Models;

final class Logger {
    private bool $debug_enable = false;
    private bool $info_enable = false;
    private bool $warning_enable = false;
    private bool $error_enable = false;

    public function __construct(private string $uri, string $log_level) {
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
                throw new \UnexpectedValueException(
                    "'{$log_level} is not supported log level'");
        }
    }

    public function debug(string $message): void {
        if ($this->debug_enable) {
            $this->output('[DEBUG] ', $message);
        }
    }

    public function info(string $message): void {
        if ($this->info_enable) {
            $this->output('[INFO] ', $message);
        }
    }

    public function warning(string $message): void {
        if ($this->warning_enable) {
            $this->output('[WARNING] ', $message);
        }
    }

    public function error(string $message): void {
        if ($this->error_enable) {
            $this->output('[ERROR] ', $message);
        }
    }

    public function catch(\Throwable $th): void {
        [$summary, $_, $trace] = explode("\n", $th->__toString(), 3);
        $this->error($summary);
        $this->debug($trace);
    }

    private function output(string $prefix, string $message): void {
        $resource = fopen($this->uri, 'a');
        if (!is_resource($resource)) {
            throw new \RuntimeException("Cannot open '{$this->uri}'");
        }

        try {
            $lines = explode("\n", $message);
            foreach ($lines as $line) {
                fwrite($resource, $prefix . $line . "\n");
            }
        } finally {
            fclose($resource);
        }
    }
}
