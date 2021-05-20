<?php declare(strict_types = 1);

namespace SpameriTests\Elastic\Data\Entity\Video;

class Technical implements \Spameri\Elastic\Entity\EntityInterface
{

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Camera|NULL
	 */
	private $camera;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime|NULL
	 */
	private $runtime;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Color|NULL
	 */
	private $color;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio|NULL
	 */
	private $ratio;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory|NULL
	 */
	private $laboratory;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength|NULL
	 */
	private $filmLength;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat|NULL
	 */
	private $negativeFormat;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess|NULL
	 */
	private $cineProcess;

	/**
	 * @var \SpameriTests\Elastic\Data\Entity\Video\Technical\Printed|NULL
	 */
	private $printed;


	public function __construct(
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Camera $camera = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime $runtime = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Color $color = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio $ratio = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory $laboratory = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength $filmLength = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat $negativeFormat = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess $cineProcess = NULL,
		?\SpameriTests\Elastic\Data\Entity\Video\Technical\Printed $printed = NULL
	)
	{
		$this->camera = $camera;
		$this->runtime = $runtime;
		$this->color = $color;
		$this->ratio = $ratio;
		$this->laboratory = $laboratory;
		$this->filmLength = $filmLength;
		$this->negativeFormat = $negativeFormat;
		$this->cineProcess = $cineProcess;
		$this->printed = $printed;
	}


	/**
	 * @return array<string, mixed>
	 */
	public function entityVariables(): array
	{
		return \get_object_vars($this);
	}


	/**
	 * @throws \Exception
	 */
	public function key(): string
	{
		return (string) \spl_object_id($this);
	}


	public function camera(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Camera
	{
		return $this->camera;
	}


	public function runtime(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Runtime
	{
		return $this->runtime;
	}


	public function color(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Color
	{
		return $this->color;
	}


	public function ratio(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Ratio
	{
		return $this->ratio;
	}


	public function laboratory(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Laboratory
	{
		return $this->laboratory;
	}


	public function filmLength(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\FilmLength
	{
		return $this->filmLength;
	}


	public function negativeFormat(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\NegativeFormat
	{
		return $this->negativeFormat;
	}


	public function cineProcess(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\CineProcess
	{
		return $this->cineProcess;
	}


	public function printed(): ?\SpameriTests\Elastic\Data\Entity\Video\Technical\Printed
	{
		return $this->printed;
	}

}
