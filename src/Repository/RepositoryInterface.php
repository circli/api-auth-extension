<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository;

use Circli\ApiAuth\Repository\Object\AuthToken;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;

interface RepositoryInterface
{
    public function createAuthObject(AuthToken $token): AuthObject;
}
