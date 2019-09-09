<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository;

use Circli\ApiAuth\Repository\Object\AccessKey;
use Circli\ApiAuth\Repository\Object\AuthToken;
use Circli\Extension\Auth\Repositories\Objects\AuthObject;
use Circli\ApiAuth\Repository\Object\AuthObject as ApiAuthObject;

class ArrayBasicAuthRepository implements BasicAuthRepository
{
	private $users = [];

	public function __construct(array $users)
	{
		foreach ($users as $user => $password) {
			$this->addUser($user, $password);
		}
	}

	public function addUser(string $user, string $password): void
	{
		if ($password[0] !== '$') {
			$password = password_hash($password, PASSWORD_DEFAULT);
		}

		$this->users[$user] = $password;
	}

	public function findByUsername(string $username): ?AuthToken
	{
		if (isset($this->users[$username])) {
			return new AccessKey($username, $this->users[$username]);
		}
		return null;
	}

	public function createAuthObject(AuthToken $token): AuthObject
	{
		return new ApiAuthObject($token, null);
	}
}
