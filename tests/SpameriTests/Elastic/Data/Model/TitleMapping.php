<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class TitleMapping extends \Spameri\Elastic\Settings\AbstractIndexConfig
{

    public function __construct(
        protected string $index = \SpameriTests\Elastic\Config::INDEX_TITLE,
        protected array $entityClass = [
            \SpameriTests\Elastic\Data\Entity\Title::class,
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
