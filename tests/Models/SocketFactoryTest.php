<?php
declare(strict_types=1);

final class SocketFactoryTest extends PHPUnit\Framework\TestCase {
    public function test_create_unix_domain_socket(): void {
        $sock = (new app\Models\SocketFactory())->create("unix:/tmp/example.sock");

        static::assertInstanceOf(app\Models\UnixDomainSocket::class, $sock);
    }

    public function test_create_scheme_not_found(): void {
        $this->expectException(UnexpectedValueException::class);

        $sock = (new app\Models\SocketFactory())->create("/tmp/example.sock");
    }

    public function test_create_unsupported_scheme(): void {
        $this->expectException(UnexpectedValueException::class);

        $sock = (new app\Models\SocketFactory())->create("ftp:/example.com/");
    }
}
