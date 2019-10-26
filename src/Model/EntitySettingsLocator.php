<?php declare(strict_types = 1);

namespace Spameri\Elastic\Model;

class EntitySettingsLocator
{

	/**
	 * @var \Spameri\Elastic\Settings\IndexConfigInterface[]
	 */
	private $indexConfig;


	public function __construct(
		\Spameri\Elastic\Settings\IndexConfigInterface ... $indexConfig
		// TODO Annotation
		// TODO Neon
	)
	{
		$this->indexConfig = $indexConfig;
	}


	public function locate($indexName) : \Spameri\ElasticQuery\Mapping\Settings
	{
		foreach ($this->indexConfig as $indexConfig) {
			if ($indexConfig->provide()->indexName() === $indexName) {
				return $indexConfig->provide();
			}
		}

		throw new \Spameri\Elastic\Exception\SettingsNotLocated($indexName);
	}

}
