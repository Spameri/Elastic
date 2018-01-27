<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;


interface ITrackedEntity
{

	public function tracking() : \Spameri\Elastic\Entity\Tracking\Tracker;

}
