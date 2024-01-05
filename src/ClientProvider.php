<?php declare(strict_types = 1);

namespace Spameri\Elastic;

class ClientProvider
{

	/**
	 * @var \Elastic\Elasticsearch\Client
	 */
	private $client;

	/**
	 * @var \Elastic\Elasticsearch\ClientBuilder
	 */
	private $clientBuilder;

	/**
	 * @var \Spameri\Elastic\SettingsProviderInterface
	 */
	private $settingsProvider;


	public function __construct(
		\Elastic\Elasticsearch\ClientBuilder $clientBuilder,
		\Spameri\Elastic\SettingsProviderInterface $settingsProvider,
	)
	{
		$this->clientBuilder = $clientBuilder;
		$this->settingsProvider = $settingsProvider;
		$this->init();
	}


	public function init(): void
	{
		$settings = $this->settingsProvider->provide();
		$this->clientBuilder->setHosts(
			[
				$settings->host() . ':' . $settings->port(),
			],
		);
		$this->clientBuilder->setConnectionParams(
			[
				'client' => [
					'headers' => $settings->headers(),
				],
			],
		);
	}


	/**
	 * @throws \Elastic\Elasticsearch\Exception\ElasticsearchException
	 */
	public function client(): \Elastic\Elasticsearch\Client
	{
		if ( ! ($this->client instanceof \Elastic\Elasticsearch\Client)) {
			$this->client = $this->clientBuilder->build();
		}

		return $this->client;
	}

}
