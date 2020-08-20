<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface TrackedEntityInterface
{

	public function tracking() : \Spameri\Elastic\Entity\Tracking\Tracker;

}
