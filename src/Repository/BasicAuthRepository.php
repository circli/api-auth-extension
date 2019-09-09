<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository;

use Circli\ApiAuth\Repository\Object\AuthToken;

interface BasicAuthRepository extends RepositoryInterface
{
	public function findByUsername(string $username): ?AuthToken;
}
