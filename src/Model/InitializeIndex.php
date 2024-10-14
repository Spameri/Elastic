<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

readonly class InitializeIndex
{

	public function __construct(
		private \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider,
		private \Spameri\Elastic\Model\Indices\Get $get,
		private \Spameri\Elastic\Model\Indices\Create $create,
		private \Spameri\Elastic\Model\Indices\AddAlias $addAlias,
	)
	{
	}


	public function execute(\Spameri\Elastic\Settings\IndexConfigInterface $indexConfig): void
	{
		$indexAlias = $indexConfig->provide()->indexName();

		try {
			$this->get->execute($indexAlias);

			throw new \Spameri\Elastic\Exception\IndexAlreadyExists($indexAlias);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			$indexName =
				$indexAlias
				. '-'
				. $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);

			$this->create->execute($indexName, $indexConfig->provide()->toArray());
			$this->addAlias->execute($indexAlias, $indexName);
		}
	}

}
