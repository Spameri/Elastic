<?php declare(strict_types = 1);

namespace Spameri\Elastic\Settings;


class NeonSettingsProvider implements \Spameri\Elastic\SettingsProviderInterface
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
		array $headers = []
	)
	{
		$this->host = $host;
		$this->port = $port;
		$this->headers = $headers;
	}


	public function provide(): \Spameri\Elastic\Settings
	{
		return new \Spameri\Elastic\Settings(
			$this->host,
			$this->port,
			$this->headers
		);
	}

}
