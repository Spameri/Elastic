<?php declare(strict_types = 1);

namespace Spameri\Elastic\Provider;


class DateTimeProvider
{

	/**
	 * @var \DateTimeImmutable
	 */
	private $constant;


	public function __construct(
		\DateTimeImmutable $constant
	)
	{
		$this->constant = $constant;
	}


	public function provide() : \DateTimeImmutable
	{
		return $this->constant;
	}

}
