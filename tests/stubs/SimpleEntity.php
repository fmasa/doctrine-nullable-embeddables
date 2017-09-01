<?php

declare(strict_types=1);

use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;
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
	 * @var Embeddable|null
	 */
	private $embeddable;


	public function __construct(Embeddable $embeddable = null)
	{
		$this->embeddable = $embeddable;
	}

	/**
	 * @return Embeddable|null
	 */
	public function getEmbeddable()
	{
		return $this->embeddable;
	}

}
