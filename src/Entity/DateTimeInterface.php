<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

interface DateTimeInterface extends \DateTimeInterface
{

	public function format($format = NULL);

}
