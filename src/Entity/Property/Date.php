<?php declare(strict_types = 1);

namespace Spameri\Elastic\Entity\Property;

class Date extends \Nette\Utils\DateTime implements \Spameri\Elastic\Entity\DateTimeInterface
{

	public const FORMAT = 'Y-m-d';


	public function format(string $format = ''): string
	{
		if ($format === '') {
			$format = self::FORMAT;
		}

		return parent::format($format);
	}

}
