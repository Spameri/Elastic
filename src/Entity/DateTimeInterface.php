<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity;

interface DateTimeInterface extends \DateTimeInterface
{

    #[\ReturnTypeWillChange]
	public function format($format = NULL);

}
