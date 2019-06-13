<?php declare(strict_types=1);

namespace Circli\ApiAuth\Provider;

use Circli\ApiAuth\Exception\InvalidArgument;
use Circli\ApiAuth\Exception\NotAuthenticated;
use Psr\Http\Message\ServerRequestInterface;

interface AuthProvider
{
	/**
	 * @param ServerRequestInterface $request
	 * @return ServerRequestInterface
	 * @throws InvalidArgument when input data is wrong
	 * @throws NotAuthenticated when authentication fails
	 */
	public function authenticate(ServerRequestInterface $request): ServerRequestInterface;
}
