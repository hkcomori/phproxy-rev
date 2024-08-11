<?php
declare(strict_types=1);

final class Http1SocketRequestTest extends PHPUnit\Framework\TestCase {
    public function test_get_from_cgi_to_string(): void {
        /**
         * Path from PATH_INFO
         */
        $env = array(
            "REQUEST_METHOD" => "GET",
            "HTTP_COOKIE" => "foo=1; bar=baz",
            "REMOTE_ADDR" => "8.8.8.8",
            "SERVER_ADDR" => "127.0.0.1",
            "SERVER_NAME" => "example.com",
            "REQUEST_SCHEME" => "https",
            "PATH_INFO" => "/path/to/hello/world",
            "QUERY_STRING" => "a=1&foo=bar",
            "SERVER_PROTOCOL" => "HTTP/1",
            "CONTENT_LENGTH" => "0",
        );
        $body = "";

        $request = app\Models\Http1SocketRequest::from_cgi($env, $body)->to_string();

        static::assertStringStartsWith(implode("\r\n", [
            "GET /path/to/hello/world?a=1&foo=bar HTTP/1",
            "Host:",
            "",
        ]), $request);

        $header_lines = [
            "Cookie: foo=1; bar=baz\r\n",
            "Content-Length: 0\r\n",
            "X-Real-IP: 8.8.8.8\r\n",
            "X-Forwarded-For: 8.8.8.8\r\n",
            "X-Forwarded-Host: example.com\r\n",
            "X-Forwarded-Proto: https\r\n",
        ];
        foreach ($header_lines as $value) {
            static::assertStringContainsString($value, $request);
        }

        static::assertStringEndsWith(implode("\r\n", [
            "",
            "",
            "",
        ]), $request);
    }

    public function test_post_from_cgi_to_string(): void {
        /**
         * Path from SCRIPT_URL
         */
        $env = array(
            "REQUEST_METHOD" => "POST",
            "HTTP_COOKIE" => "foo=1; bar=baz",
            "HTTP_X_REAL_IP" => "192.168.0.1",
            "HTTP_X_FORWARDED_FOR" => "192.168.0.1",
            "HTTP_X_FORWARDED_HOST" => "example.com",
            "HTTP_X_FORWARDED_PROTO" => "http",
            "REMOTE_ADDR" => "192.168.0.2",
            "SERVER_ADDR" => "127.0.0.1",
            "SERVER_NAME" => "proxy.example.com",
            "REQUEST_SCHEME" => "http",
            "SCRIPT_URL" => "/path/to/hello/world",
            "SERVER_PROTOCOL" => "HTTP/1.1",
            "HTTP_HOST" => "example.com",
            "CONTENT_LENGTH" => "12",
        );
        $body = "hello world!";

        $request = app\Models\Http1SocketRequest::from_cgi($env, $body)->to_string();

        static::assertStringStartsWith(implode("\r\n", [
            "POST /path/to/hello/world HTTP/1.1",
            "Host: example.com",
            "",
        ]), $request);

        $header_lines = [
            "Cookie: foo=1; bar=baz\r\n",
            "Content-Length: 12\r\n",
            "X-Real-IP: 192.168.0.2\r\n",
            "X-Forwarded-For: 192.168.0.1, 192.168.0.2\r\n",
            "X-Forwarded-Host: proxy.example.com\r\n",
            "X-Forwarded-Proto: http\r\n",
        ];
        foreach ($header_lines as $value) {
            static::assertStringContainsString($value, $request);
        }

        static::assertStringEndsWith(implode("\r\n", [
            "",
            "",
            "hello world!",
        ]), $request);
    }
}
