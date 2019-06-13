<?php

namespace Tests\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Provider\AccessKeyProvider;
use Circli\ApiAuth\Repository\AccessKeyRepository;
use Circli\ApiAuth\Repository\Object\AccessKey;
use Circli\ApiAuth\RequestAttributeKeys;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class AccessKeyProviderTest extends TestCase
{
	public function testSuccessfulAuthentication(): void
	{
		$apiKey = 'testapikey1243';
		$apiToken = 'test-token';

		$accessKey = new AccessKey($apiKey, password_hash($apiToken, PASSWORD_DEFAULT));

		$repository = $this->createMock(AccessKeyRepository::class);
		$repository->expects($this->once())->method('findByApiKey')->with($apiKey)->willReturn($accessKey);
		$provider = new AccessKeyProvider($repository);

		$request = $this->createMock(ServerRequestInterface::class);

		$request
			->expects($this->once())
			->method('getHeaderLine')
			->with('X-Api-Key')
			->willReturn($apiKey . '.' . $apiToken);

		$request->expects($this->exactly(2))
			->method('withAttribute')
			->withConsecutive(
				[RequestAttributeKeys::AUTHENTICATED, true],
				[AccessKeyProvider::TOKEN_KEY, $accessKey]
			)
			->willReturn($request);

		$provider->authenticate($request);
	}

	public function testMalformedKey(): void
	{
		//todo add provider to test keys
		$key = 'test';

		$repository = $this->createMock(AccessKeyRepository::class);
		$repository->expects($this->never())->method('findByApiKey');
		$provider = new AccessKeyProvider($repository);

		$request = $this->createMock(ServerRequestInterface::class);

		$request
			->expects($this->once())
			->method('getHeaderLine')
			->with('X-Api-Key')
			->willReturn($key);

		$request->expects($this->never())
			->method('withAttribute');

		$this->expectException(InvalidArgument::class);

		$provider->authenticate($request);
	}

	public function testMissingKey(): void
	{
		$repository = $this->createMock(AccessKeyRepository::class);
		$repository->expects($this->never())->method('findByApiKey');
		$provider = new AccessKeyProvider($repository);

		$request = $this->createMock(ServerRequestInterface::class);

		$request
			->expects($this->once())
			->method('getHeaderLine')
			->with('X-Api-Key')
			->willReturn(null);

		$request->expects($this->never())
			->method('withAttribute');

		$this->expectException(InvalidArgument::class);

		$provider->authenticate($request);
	}

	public function testNotAuthenticated(): void
	{
		$apiKey = 'testapikey1243';
		$apiToken = 'test-token';

		$repository = $this->createMock(AccessKeyRepository::class);
		$repository->expects($this->once())->method('findByApiKey')->with($apiKey)->willReturn(null);
		$provider = new AccessKeyProvider($repository);

		$request = $this->createMock(ServerRequestInterface::class);
		$request
			->expects($this->once())
			->method('getHeaderLine')
			->with('X-Api-Key')
			->willReturn($apiKey . '.' . $apiToken);

		$request->expects($this->never())
			->method('withAttribute');

		$this->expectException(NotAuthenticated::class);

		$provider->authenticate($request);
	}
}
