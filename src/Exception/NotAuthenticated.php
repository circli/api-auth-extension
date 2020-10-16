<?php declare(strict_types=1);

namespace Circli\ApiAuth\Exception;

class NotAuthenticated extends \DomainException
{
    /** @var array<string, mixed> */
    private array $data;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(string $message, array $data = [])
    {
        parent::__construct($message);
        $this->data = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
