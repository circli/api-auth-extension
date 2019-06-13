<?php declare(strict_types=1);

namespace Circli\ApiAuth\Middleware;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Circli\ApiAuth\Provider\AuthProvider;
use Circli\Core\Middleware\LoggerResponseTrait;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ApiAuthenticationMiddleware implements MiddlewareInterface
{
	use LoggerResponseTrait;

	/** @var LoggerInterface */
	private $logger;
	/** @var AuthProvider */
	private $authProvider;

	public function __construct(
		AuthProvider $authProvider,
		ResponseFactoryInterface $responseFactory,
		LoggerInterface $logger = null
	) {
		$this->logger = $logger ?? new NullLogger();
		$this->authProvider = $authProvider;
		$this->responseFactory = $responseFactory;
	}

	/**
	 * @inheritdoc
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		try {
			$request = $this->authProvider->authenticate($request);
			return $handler->handle($request);
		}
		catch (InvalidArgument $e) {
			return $this->handleWarning($e->getMessage(), $e->getData());
		}
		catch (NotAuthenticated $e) {
			return $this->handleError($e->getMessage(), $e->getData(), 403);
		}
	}
}
