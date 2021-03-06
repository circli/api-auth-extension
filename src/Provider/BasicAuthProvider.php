<?php declare(strict_types=1);

namespace Circli\ApiAuth\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Repository\BasicAuthRepository;
use Circli\ApiAuth\RequestAttributeKeys;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;
use Circli\Extension\Auth\Repositories\Objects\NullAuthObject;
use Psr\Http\Message\ServerRequestInterface;

final class BasicAuthProvider implements AuthProvider
{
    public const TOKEN_KEY = 'circli:api-auth:provider:token';

    /** @var BasicAuthRepository */
    private $repository;
    /** @var \Circli\ApiAuth\Repository\Object\AuthToken|null */
    private $authToken;

    public function __construct(BasicAuthRepository $repository)
    {
        $this->repository = $repository;
    }

    public function authenticate(ServerRequestInterface $request): ServerRequestInterface
    {
        $header = $request->getHeaderLine('Authorization');
        if (!$header) {
            throw new InvalidArgument('Missing Authorization header');
        }
        if (preg_match("/Basic\s+(.*)$/i", $header, $matches)) {
            [$user, $password] = explode(':', base64_decode($matches[1]), 2);

            $token = $this->repository->findByUsername($user);

            if (!$token || !$token->isValid($password)) {
                throw new NotAuthenticated('Failed to authenticate api token', [
                    'user' => $user,
                ]);
            }
            $this->authToken = $token;

            $request = $request->withAttribute(RequestAttributeKeys::AUTHENTICATED, true);
            return $request->withAttribute(self::TOKEN_KEY, $token);
        }

        throw new InvalidArgument('Malformed Authorization header. Only supports "Basic"');
    }

    public function getAuthObject(): AuthObject
    {
        if (!$this->authToken) {
            return new NullAuthObject();
        }

        return $this->repository->createAuthObject($this->authToken);
    }
}
