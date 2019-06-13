<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository\Object;

final class AccessKey implements AuthToken
{
	/** @var string */
	private $key;
	/** @var string */
	private $hashedToken;

	public function __construct(string $key, string $hashedToken)
	{
		$this->key = $key;
		$this->hashedToken = $hashedToken;
	}

	public function getKey(): string
	{
		return $this->key;
	}

	public function isValid(string $apiToken): bool
	{
		return password_verify($apiToken, $this->hashedToken);
	}
}
