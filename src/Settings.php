<?php declare(strict_types = 1);

namespace Spameri\Elastic;

class Settings
{

	/**
	 * @var string
	 */
	private $host;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var array<mixed>
	 */
	private $headers;


	/**
	 * @param array<mixed> $headers
	 */
	public function __construct(
		string $host,
		int $port,
		array $headers
	)
	{
		$this->host = $host;
		$this->port = $port;
		$this->headers = $headers;
	}


	public function host(): string
	{
		return $this->host;
	}


	public function port(): int
	{
		return $this->port;
	}


	/**
	 * @return array<mixed>
	 */
	public function headers(): array
	{
		return $this->headers;
	}

}
