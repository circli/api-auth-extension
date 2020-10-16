<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository\Object;

final class AuthObject implements \Circli\Extension\Auth\Repositories\Objects\AuthObject
{
    private AuthToken $token;
    private $data;

    public function __construct(AuthToken $token, $data)
    {
        $this->token = $token;
        $this->data = $data;
    }

    public function getToken(): AuthToken
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
