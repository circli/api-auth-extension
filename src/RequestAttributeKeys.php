<?php declare(strict_types=1);

namespace Circli\ApiAuth;

interface RequestAttributeKeys extends \Circli\Extension\Auth\Web\RequestAttributeKeys
{
	public const AUTHENTICATED = 'circli:api-auth:authenticated';
}
