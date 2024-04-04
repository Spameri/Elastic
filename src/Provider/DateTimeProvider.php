<?php declare(strict_types = 1);

namespace Spameri\Elastic\Provider;

readonly class DateTimeProvider
{

	public function __construct(
		private \DateTimeImmutable $constant,
	)
	{
	}


	public function provide(): \DateTimeImmutable
	{
		return $this->constant;
	}

}
