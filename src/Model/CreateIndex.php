<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class CreateIndex
{

	public function __construct(
		private \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider,
		private \Spameri\Elastic\Model\Indices\AddAlias $addAlias,
		private \Spameri\Elastic\Model\Indices\Get $get,
		private \Spameri\Elastic\Model\Indices\Create $create,
	)
	{
	}


	public function execute(string $index): void
	{
		try {
			$this->get->execute($index);

			throw new \Spameri\Elastic\Exception\IndexAlreadyExists($index);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			$indexName = $index . '-'
				. $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);

			$this->create->execute($indexName, [], $index);
			$this->addAlias->execute($index, $indexName);
		}
	}

}
