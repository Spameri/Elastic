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
		private string|null $username,
		private string|null $password,
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


	public function username(): string|null
	{
		return $this->username;
	}


	public function password(): string|null
	{
		return $this->password;
	}


	/**
	 * @return array<mixed>
	 */
	public function headers(): array
	{
		return $this->headers;
	}

}
