<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Model;

class ImageMapping extends \Spameri\Elastic\Settings\AbstractIndexConfig
{

    public function __construct(
        protected string $index = \SpameriTests\Elastic\Config::INDEX_IMAGE,
        protected array $entityClass = [
            \SpameriTests\Elastic\Data\Entity\Image::class,
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
