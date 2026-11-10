<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class EdgeCaseMapping extends \Spameri\Elastic\Settings\AbstractIndexConfig
{

	public function __construct(
		protected string $index = \SpameriTests\Elastic\Config::INDEX_EDGE_CASE,
		protected array $entityClass = [
			\SpameriTests\Elastic\Data\Entity\EdgeCaseEntity::class,
		],
	)
	{
		parent::__construct($index, $entityClass);
	}

	public function provide(): \Spameri\ElasticQuery\Mapping\Settings
	{
		return new \Spameri\ElasticQuery\Mapping\Settings($this->index);
	}

}
