<?php
declare(strict_types=1);
namespace app\Models;

final class CurlExecException extends \RuntimeException {
    public function __construct(\CurlHandle $ch) {
        parent::__construct();
        $this->code = curl_errno($ch);
        $this->message = curl_error($ch);
    }
}
