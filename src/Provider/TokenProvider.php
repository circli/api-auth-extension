<?php declare(strict_types=1);

namespace Circli\ApiAuth\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Repository\AuthTokenRepository;
use Circli\ApiAuth\Repository\Object\AuthToken;
use Circli\ApiAuth\RequestAttributeKeys;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;
use Circli\Extension\Auth\Repositories\Objects\NullAuthObject;
use Psr\Http\Message\ServerRequestInterface;
use function strlen;

final class TokenProvider implements AuthProvider
{
    public const TOKEN_KEY = 'circli:api-auth:provider:token';

    private AuthTokenRepository $repository;
    private ?AuthToken $authToken;

    public function __construct(AuthTokenRepository $repository)
    {
        $this->repository = $repository;
    }

    public function authenticate(ServerRequestInterface $request): ServerRequestInterface
    {
        $apiId = (int)$request->getHeaderLine('X-Api-Id');
        $apiToken = $request->getHeaderLine('X-Api-Token');

        if (!$apiId) {
            throw new InvalidArgument('Missing api id');
        }
        if ($apiId <= 0) {
            throw new InvalidArgument('Malformed api id', [
                'id' => $apiId,
            ]);
        }

        if (!$apiToken) {
            throw new InvalidArgument('Missing api token');
        }

        if (strlen($apiToken) <= 11) {
            throw new InvalidArgument('Malformed api token', [
                'length' => strlen($apiToken),
            ]);
        }

        $authTokenObject = $this->repository->findByApiId($apiId);

        if (!$authTokenObject || !$authTokenObject->isValid($apiToken)) {
            throw new NotAuthenticated('Failed to authenticate api token', [
                'api_id' => $apiId,
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
