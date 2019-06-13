<?php declare(strict_types=1);

namespace Circli\ApiAuth\Exception;

class InvalidArgument extends \InvalidArgumentException
{
	/** @var array */
	private $data;

	public function __construct($message, array $data = [])
	{
		parent::__construct($message);
		$this->data = $data;
	}

	public function getData(): array
	{
		return $this->data;
	}
}
