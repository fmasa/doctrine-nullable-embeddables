<?php

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Embeddable
 */
class Embeddable
{

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $string;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $int;


	public function __construct(string $string = null, int $int = null)
	{
		$this->string = $string;
		$this->int = $int;
	}

}
