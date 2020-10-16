<?php declare(strict_types=1);

namespace Circli\ApiAuth\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Repository\AccessKeyRepository;
use Circli\ApiAuth\Repository\Object\AuthToken;
use Circli\ApiAuth\RequestAttributeKeys;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;
use Circli\Extension\Auth\Repositories\Objects\NullAuthObject;
use Psr\Http\Message\ServerRequestInterface;

final class AccessKeyProvider implements AuthProvider
{
    public const TOKEN_KEY = 'circli:api-auth:provider:token';

    private AccessKeyRepository $repository;
    private AuthToken $authToken;

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

        $this->authToken = $authTokenObject;

        $request = $request->withAttribute(RequestAttributeKeys::AUTHENTICATED, true);
        return $request->withAttribute(self::TOKEN_KEY, $authTokenObject);
    }

    public function getAuthObject(): AuthObject
    {
        if (!$this->authToken) {
            return new NullAuthObject();
        }

        return $this->repository->createAuthObject($this->authToken);
    }
}
