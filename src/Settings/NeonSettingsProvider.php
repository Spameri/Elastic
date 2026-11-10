<?php declare(strict_types = 1);

namespace Spameri\Elastic\Settings;

readonly class NeonSettingsProvider implements \Spameri\Elastic\SettingsProviderInterface
{

	/**
	 * @param array<mixed> $headers
	 */
	public function __construct(
		private string $host,
		private int $port,
		private string|null $username,
		private string|null $password,
		private array $headers = [],
	)
	{
	}


	public function provide(): \Spameri\Elastic\Settings
	{
		return new \Spameri\Elastic\Settings(
			$this->host,
			$this->port,
			$this->username,
			$this->password,
			$this->headers,
		);
	}

}
