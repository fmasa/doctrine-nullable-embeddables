<?php

use Doctrine\ORM\Mapping as ORM;
use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;


/**
 * @ORM\Embeddable
 */
class NestedEmbeddable
{

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	private $string;

	/**
	 * @ORM\Embedded(class="Embeddable")
	 * @Nullable
	 * @var Embeddable|NULL
	 */
	private $embeddable;


	public function __construct(string $string = NULL, Embeddable $embeddable = NULL)
	{
		$this->string = $string;
		$this->embeddable = $embeddable;
	}


	/**
	 * @return Embeddable|NULL
	 */
	public function getEmbeddable()
	{
		return $this->embeddable;
	}

}
