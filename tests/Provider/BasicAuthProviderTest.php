<?php

namespace Tests\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Provider\BasicAuthProvider;
use Circli\ApiAuth\Repository\ArrayBasicAuthRepository;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class BasicAuthProviderTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $provider = new BasicAuthProvider(new ArrayBasicAuthRepository(['test' => 'password']));

        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())->method('getHeaderLine')->with('Authorization')->willReturn('Basic ' . base64_encode('test:password'));
        $request->expects($this->exactly(2))->method('withAttribute')->willReturn($request);

        $provider->authenticate($request);
    }

    public function testUserNotFound(): void
    {
        $provider = new BasicAuthProvider(new ArrayBasicAuthRepository([]));

        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())->method('getHeaderLine')->with('Authorization')->willReturn('Basic ' . base64_encode('test:password'));
        $request->expects($this->never())->method('withAttribute');

        $this->expectException(NotAuthenticated::class);

        $provider->authenticate($request);
    }

    public function testMissingHeader(): void
    {
        $provider = new BasicAuthProvider(new ArrayBasicAuthRepository([]));

        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())->method('getHeaderLine')->with('Authorization')->willReturn(null);
        $request->expects($this->never())->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }

    public function testInvalidAuthorizationType(): void
    {
        $provider = new BasicAuthProvider(new ArrayBasicAuthRepository([]));

        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->once())->method('getHeaderLine')->with('Authorization')->willReturn('Bearer test');
        $request->expects($this->never())->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }
}
