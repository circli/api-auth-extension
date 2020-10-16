<?php declare(strict_types=1);

namespace Circli\ApiAuth\Repository\Object;

final class Token implements AuthToken
{
    /** @var int */
    private $id;
    /** @var string */
    private $apiKey;

    public function __construct(int $id, string $apiKey)
    {
        $this->id = $id;
        $this->apiKey = $apiKey;
    }

    public function isValid(string $apiToken): bool
    {
        return hash_equals($this->apiKey, $apiToken);
    }

    public function getId(): int
    {
        return $this->id;
    }
}
