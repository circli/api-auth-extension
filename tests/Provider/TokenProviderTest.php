<?php

namespace Tests\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Provider\AccessKeyProvider;
use Circli\ApiAuth\Provider\TokenProvider;
use Circli\ApiAuth\Repository\AuthTokenRepository;
use Circli\ApiAuth\Repository\Object\Token;
use Circli\ApiAuth\RequestAttributeKeys;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class TokenProviderTest extends TestCase
{
    public function testSuccessfulAuthentication(): void
    {
        $apiId = 1;
        $apiToken = 'testtokentest';

        $token = new Token($apiId, $apiToken);

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->once())->method('findByApiId')->with($apiId)->willReturn($token);
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls((string)$apiId, $apiToken);

        $request->expects($this->exactly(2))
            ->method('withAttribute')
            ->withConsecutive(
                [RequestAttributeKeys::AUTHENTICATED, true],
                [AccessKeyProvider::TOKEN_KEY, $token]
            )
            ->willReturn($request);

        $provider->authenticate($request);
    }

    public function testMissingApiId(): void
    {
        $apiId = 1;
        $apiToken = 'testtokentest';

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->never())->method('findByApiId');
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls(null, $apiToken);

        $request->expects($this->never())
            ->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }

    public function testMalformedApiId(): void
    {
        $apiId = -1;
        $apiToken = 'testtokentest';

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->never())->method('findByApiId');
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls($apiId, $apiToken);

        $request->expects($this->never())
            ->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }

    public function testMissingToken(): void
    {
        $apiId = 1;
        $apiToken = null;

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->never())->method('findByApiId');
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls($apiId, $apiToken);

        $request->expects($this->never())
            ->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }

    public function testMalformedToken(): void
    {
        $apiId = 1;
        $apiToken = 'toshort';

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->never())->method('findByApiId');
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls($apiId, $apiToken);

        $request->expects($this->never())
            ->method('withAttribute');

        $this->expectException(InvalidArgument::class);

        $provider->authenticate($request);
    }

    public function testNotAuthenticated()
    {
        $apiId = 1;
        $apiToken = 'testtokentest';

        $token = new Token($apiId, $apiToken);

        $repository = $this->createMock(AuthTokenRepository::class);
        $repository->expects($this->once())->method('findByApiId')->with($apiId)->willReturn(null);
        $provider = new TokenProvider($repository);

        $request = $this->createMock(ServerRequestInterface::class);

        $request
            ->expects($this->exactly(2))
            ->method('getHeaderLine')
            ->withConsecutive(['X-Api-Id'], ['X-Api-Token'])
            ->willReturnOnConsecutiveCalls((string)$apiId, $apiToken);

        $request->expects($this->never())
            ->method('withAttribute');

        $this->expectException(NotAuthenticated::class);

        $provider->authenticate($request);
    }
}
