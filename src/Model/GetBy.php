<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;


class GetBy
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


	/**
	 * @throws \Spameri\Elastic\Exception\DocumentNotFound
	 */
	public function execute(
		\Spameri\ElasticQuery\ElasticQuery $options
		, string $index
	) : array
	{
		$documents = $this->clientProvider->client()->search(
			(
				new \Spameri\ElasticQuery\Document(
					$index,
					new \Spameri\ElasticQuery\Document\Body\Plain($options->toArray()),
					$index
				)
			)
				->toArray()
		);

		$data = NULL;
		if ($documents['hits']['total']) {
			$data = $documents['hits']['hits'][0]['_source'];
			$data['id'] = $documents['hits']['hits'][0]['_id'];
		}

		if ( ! $data) {
			throw new \Spameri\Elastic\Exception\DocumentNotFound($index, $options);
		}

		return $data;
	}
}
