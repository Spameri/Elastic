<?php declare(strict_types = 1);

namespace Spameri\Elastic\Settings;


class NeonSettingsProvider implements \Spameri\Elastic\SettingsProviderInterface
{

	/**
	 * @var array
	 */
	private $parameters;


	public function fromArray(array $parameters) : void
	{
		$this->parameters = $parameters;
	}


	public function fromFile(string $file) : void
	{
		$this->parameters = \Nette\Neon\Neon::decode($file);
	}


	/**
	 * @throws \RuntimeException
	 */
	public function provide() : \Spameri\Elastic\Settings
	{
		if ( ! $this->parameters) {
			throw new \RuntimeException(
				'Settings from neon was not set, please initialize parameters with function '
						. '`' . \Spameri\Elastic\Settings\NeonSettingsProvider::class . '::fromArray` or '
						. '`' . \Spameri\Elastic\Settings\NeonSettingsProvider::class . '::fromFile`'
			);
		}

		return new \Spameri\Elastic\Settings(
			$this->parameters['host'],
			$this->parameters['port'],
			$this->parameters['headers'] ?? []
		);
	}

}
