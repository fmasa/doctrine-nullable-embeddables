<?php

use Doctrine\ORM\Mapping as ORM;
use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;


/**
 * @ORM\Entity()
 */
class EntityWithNestedEmbeddable
{

	/**
	 * @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Embedded(class="NestedEmbeddable")
	 * @Nullable
	 * @var NestedEmbeddable|null
	 */
	private $embeddable;


	public function __construct(NestedEmbeddable $embeddable = null)
	{
		$this->embeddable = $embeddable;
	}

	/**
	 * @return NestedEmbeddable|null
	 */
	public function getEmbeddable()
	{
		return $this->embeddable;
	}

}
