<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository;

use Circli\ApiAuth\Repository\Object\AuthToken;

interface AuthTokenRepository
{
	public function findByApiId(int $apiId): ?AuthToken;
}
