<?php declare(strict_types = 1);

namespace Spameri\Elastic;


class ClientProvider
{

	/**
	 * @var \Elasticsearch\ClientBuilder
	 */
	private $clientBuilder;
	/**
	 * @var \Spameri\Elastic\SettingsProviderInterface
	 */
	private $settingsProvider;


	public function __construct(
		\Elasticsearch\ClientBuilder $clientBuilder
		, \Spameri\Elastic\SettingsProviderInterface $settingsProvider
	)
	{
		$this->clientBuilder = $clientBuilder;
		$this->settingsProvider = $settingsProvider;
		$this->init();
	}


	public function init() : void
	{
		$settings = $this->settingsProvider->provide();
		$this->clientBuilder->setHosts([
			$settings->host() . ':' . $settings->port()
		]);
		$this->clientBuilder->setConnectionParams([
			'client' => [
				'headers' => $settings->headers(),
			],
		]);
	}


	public function client() : \Elasticsearch\Client
	{
		return $this->clientBuilder->build();
	}

}
