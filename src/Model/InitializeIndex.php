<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class InitializeIndex
{

	private \Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider;

	private \Spameri\Elastic\Model\Indices\Get $get;

	private \Spameri\Elastic\Model\Indices\Create $create;

	private \Spameri\Elastic\Model\Indices\AddAlias $addAlias;


	public function __construct(
		\Spameri\Elastic\Provider\DateTimeProvider $dateTimeProvider,
		\Spameri\Elastic\Model\Indices\Get $get,
		\Spameri\Elastic\Model\Indices\Create $create,
		\Spameri\Elastic\Model\Indices\AddAlias $addAlias,
	)
	{
		$this->dateTimeProvider = $dateTimeProvider;
		$this->get = $get;
		$this->create = $create;
		$this->addAlias = $addAlias;
	}


	public function execute(\Spameri\Elastic\Settings\IndexConfigInterface $indexConfig): void
	{
		$indexAlias = $indexConfig->provide()->indexName();

		try {
			$this->get->execute($indexAlias);

			throw new \Spameri\Elastic\Exception\IndexAlreadyExists($indexAlias);

		} catch (\Spameri\Elastic\Exception\ElasticSearch $exception) {
			$indexName =
				$indexAlias . '-'
				. $this->dateTimeProvider->provide()->format(\Spameri\Elastic\Entity\Property\DateTime::INDEX_FORMAT);

			$this->create->execute($indexName, $indexConfig->provide()->toArray(), $indexAlias);
			$this->addAlias->execute($indexAlias, $indexName);
		}
	}

}
