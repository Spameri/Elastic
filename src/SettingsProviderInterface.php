<?php declare(strict_types = 1);

namespace Spameri\Elastic;


interface SettingsProviderInterface
{

	public function provide() : \Spameri\Elastic\Settings;

}
