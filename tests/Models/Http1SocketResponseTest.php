<?php
declare(strict_types=1);

final class Http1SocketResponseTest extends PHPUnit\Framework\TestCase {
    public function test_from_string(): void {
        $response = implode("\r\n", [
            "HTTP/1.1 200 OK",
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "hello world!",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);

        static::assertSame(200, $parsed_response->status_code);

        $header_lines = [
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
        ];
        foreach ($header_lines as $value) {
            static::assertContains($value, $parsed_response->header_lines);
        }

        static::assertSame("hello world!", $parsed_response->body);
    }

    public function test_from_string_without_status_line(): void {
        $this->expectException(UnexpectedValueException::class);

        $response = implode("\r\n", [
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "hello world!",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);
    }

    public function test_from_string_with_illigal_prptocol(): void {
        $this->expectException(UnexpectedValueException::class);

        $response = implode("\r\n", [
            "FTP 200 OK",
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "hello world!",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);
    }

    public function test_from_string_with_illigal_status_code(): void {
        $this->expectException(UnexpectedValueException::class);

        $response = implode("\r\n", [
            "FTP/1.1 99 OK",
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "hello world!",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);

        $response = implode("\r\n", [
            "HTTP/1.1 1000 OK",
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 12",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "hello world!",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);
    }

    public function test_from_string_without_body(): void {
        $response = implode("\r\n", [
            "HTTP/1.1 200 OK",
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 0",
            "Content-Type: text/plain; charset=utf-8",
            "",
            "",
        ]);
        $parsed_response = app\Models\Http1SocketResponse::from_string($response);

        static::assertSame(200, $parsed_response->status_code);
        static::assertSame([
            "Set-Cookie: foo=1; bar=baz",
            "Content-Length: 0",
            "Content-Type: text/plain; charset=utf-8",
        ], $parsed_response->header_lines);
        static::assertSame("", $parsed_response->body);
    }
}
