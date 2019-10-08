<?php declare(strict_types = 1);

namespace Spameri\Elastic\Settings;

interface IndexConfigInterface
{

	public function provide() : \Spameri\ElasticQuery\Mapping\Settings;

}
