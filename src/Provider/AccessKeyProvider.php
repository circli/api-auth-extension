<?php declare(strict_types=1);

namespace Circli\ApiAuth\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Repository\AccessKeyRepository;
use Circli\ApiAuth\Repository\AuthTokenRepository;
use Circli\ApiAuth\RequestAttributeKeys;
use Psr\Http\Message\ServerRequestInterface;

final class AccessKeyProvider implements AuthProvider
{
	public const TOKEN_KEY = 'circli:api-auth:provider:token';

	/** @var AccessKeyRepository */
	private $repository;

	public function __construct(AccessKeyRepository $repository)
	{
		$this->repository = $repository;
	}

	public function authenticate(ServerRequestInterface $request): ServerRequestInterface
	{
		$apiKey = $request->getHeaderLine('X-Api-Key');
		if (!$apiKey) {
			throw new InvalidArgument('Missing api key');
		}

		if (strlen($apiKey) < 11 || strpos($apiKey, '.', 10) === false) {
			throw new InvalidArgument('Malformed api key');
		}

		[$key, $token] = explode('.', $apiKey);

		$authTokenObject = $this->repository->findByApiKey($key);

		if (!$authTokenObject || !$authTokenObject->isValid($token)) {
			throw new NotAuthenticated('Failed to authenticate api token', [
				'key' => $key,
			]);
		}

		$request = $request->withAttribute(RequestAttributeKeys::AUTHENTICATED, true);
		return $request->withAttribute(self::TOKEN_KEY, $authTokenObject);
	}
}
