<?php declare(strict_types = 1);

namespace Spameri\Elastic;

readonly class Settings
{

	/**
	 * @param array<mixed> $headers
	 */
	public function __construct(
		private string $host,
		private int $port,
		private array $headers,
	)
	{
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
