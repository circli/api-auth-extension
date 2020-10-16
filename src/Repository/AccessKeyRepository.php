<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository;

use Circli\ApiAuth\Repository\Object\AuthToken;

interface AccessKeyRepository extends RepositoryInterface
{
    public function findByApiKey(string $apiId): ?AuthToken;
}
