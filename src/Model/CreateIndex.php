<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class CreateIndex
{

	private \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider;

	private Indices\AddAlias $addAlias;

	private Indices\Get $get;

	private Indices\Create $create;


	public function __construct(
		\Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider,
		\Spameri\Elastic\Model\Indices\AddAlias $addAlias,
		\Spameri\Elastic\Model\Indices\Get $get,
		\Spameri\Elastic\Model\Indices\Create $create
	)
	{
		$this->dateTimeProvider = $dateTimeProvider;
		$this->addAlias = $addAlias;
		$this->get = $get;
		$this->create = $create;
	}


	public function execute(string $index): void
	{
		try {
			$this->get->execute($index);

			throw new \Spameri\Elastic\Exception\IndexAlreadyExists($index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			$indexName = $index . '-'
				. $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);

			$this->create->execute($indexName, []);
			$this->addAlias->execute($index, $indexName);
		}
	}

}
