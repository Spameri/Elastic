<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class GetAllBy
{

	/**
	 * @var \Spameri\Elastic\ClientProvider
	 */
	private $clientProvider;


	public function __construct(
		\Spameri\Elastic\ClientProvider $clientProvider
	)
	{
		$this->clientProvider = $clientProvider;
	}


	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $options,
		string $index
	) : array
	{
		$result = $this->clientProvider->client()->search(
			(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain($options->toArray()),
					$index
				)
			)
				->toArray()
		);

		return $result['hits'];
	}
}
