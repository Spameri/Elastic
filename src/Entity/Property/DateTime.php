<?php declare(strict_types=1);

namespace Spameri\Elastic\Entity\Property;

class DateTime extends \Nette\Utils\DateTime implements \Spameri\Elastic\Entity\DateTimeInterface
{

	public const FORMAT = 'Y-m-d\TH:i:s';
	public const INDEX_FORMAT = 'Y-m-d_H-i-s';


	/**
	 * @param string|null $format
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function format($format = NULL)
	{
		if (\is_null($format)) {
			$format = self::FORMAT;
		}

		return parent::format($format);
	}


	public function formatTimeAgo(): string
	{
		$timeDifference = \time() - $this->format('U');

		if ($timeDifference < 1) {
			return 'less than 1 second ago';
		}

		$condition = [
			12 * 30 * 24 * 60 * 60 => 'year',
			30 * 24 * 60 * 60 => 'month',
			24 * 60 * 60 => 'day',
			60 * 60 => 'hour',
			60 => 'minute',
			1 => 'second',
		];

		foreach ($condition as $secs => $str) {
			$d = $timeDifference / $secs;

			if ($d >= 1) {
				$t = \round($d);
				return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
			}
		}

		return 'some time ago';
	}

}
