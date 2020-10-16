<?php declare(strict_types=1);

namespace Circli\ApiAuth\Middleware;

use Circli\ApiAuth\ApiAuth;
use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Provider\AuthProvider;
use Circli\ApiAuth\RequestAttributeKeys;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ApiAuthenticationMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private AuthProvider $authProvider;
    private EventDispatcherInterface $eventDispatcher;
    private ResponseFactoryInterface $responseFactory;

    public function __construct(
        AuthProvider $authProvider,
        ResponseFactoryInterface $responseFactory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->authProvider = $authProvider;
        $this->responseFactory = $responseFactory;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            $request = $this->authProvider->authenticate($request);
            $request = $request->withAttribute(
                RequestAttributeKeys::AUTH,
                new ApiAuth($this->authProvider->getAuthObject(), $this->eventDispatcher)
            );
            return $handler->handle($request);
        }
        catch (InvalidArgument $e) {
            $this->logger->warning($e->getMessage(), $e->getData());
            return $this->responseFactory->createResponse(400)->withHeader('X-Api-Error', $e->getMessage());
        }
        catch (NotAuthenticated $e) {
            $this->logger->error($e->getMessage(), $e->getData());
            return $this->responseFactory->createResponse(403);
        }
    }
}
