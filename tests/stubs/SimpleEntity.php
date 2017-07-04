<?php

use Fmasa\DoctrineNullableEmbeddables\NullableAnnotation as Nullable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class SimpleEntity
{

	/**
	 * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Embedded(class="Embeddable")
	 * @Nullable
	 * @var Embeddable|NULL
	 */
	private $embeddable;


	public function __construct(Embeddable $embeddable = NULL)
	{
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
