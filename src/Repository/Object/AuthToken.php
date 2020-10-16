<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository\Object;

interface AuthToken
{
    public function isValid(string $apiToken): bool;
}
