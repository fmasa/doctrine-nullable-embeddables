<?php

namespace Fmasa\DoctrineNullableEmbeddables;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Tools\Setup;
use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;
use PHPUnit\Framework\TestCase;


class SubscriberTest extends TestCase
{

	/** @var Subscriber */
	private $subscriber;

	public function setUp()
	{
		class_exists(Nullable::class); // autoload annotation
		$this->subscriber = new Subscriber(new AnnotationReader());
	}

	public function testSimpleNullEmbeddable()
	{
		$entity = new \SimpleEntity(new \Embeddable(null, null));

		$this->callPostLoad($entity);

		$this->assertNull($entity->getEmbeddable());
	}

	public function testSimpleNotNullEmbeddable()
	{
		$embeddable = new \Embeddable("string", 0);
		$entity = new \SimpleEntity($embeddable);

		$this->callPostLoad($entity);

		$this->assertEquals($embeddable, $entity->getEmbeddable());
	}

	public function testNestedNullEmbeddable()
	{
		$entity = new \EntityWithNestedEmbeddable(
			new \NestedEmbeddable(
				null,
				new \Embeddable(null, null)
			)
		);

		$this->callPostLoad($entity);

		$this->assertNull($entity->getEmbeddable());
	}

	public function testNestedNotNullEmbeddable()
	{
		$embeddable = new \NestedEmbeddable('string', new \Embeddable('string', 20));
		$entity = new \EntityWithNestedEmbeddable($embeddable);

		$this->callPostLoad($entity);

		$this->assertEquals($embeddable, $entity->getEmbeddable());
	}

	public function testPartialyLoadedEntity()
	{
		$entity = new \SimpleEntity(null);

		$this->callPostLoad($entity);

		$this->assertNull($entity->getEmbeddable());
	}

	public function callPostLoad($entity)
	{
		$connectionParameters = [
			'driver' => 'pdo_sqlite',
		];
		$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/stubs'], true, null, null, false);
		$entityManager = EntityManager::create($connectionParameters, $config);
		/** @var \Doctrine\ORM\Mapping\Driver\AnnotationDriver $annotationDriver */
		$annotationDriver = $entityManager->getConfiguration()->getMetadataDriverImpl();
		$subscriber = new Subscriber($annotationDriver->getReader());
		$loadEvent = new LifecycleEventArgs($entity, $entityManager);
		$subscriber->postLoad($loadEvent);
	}

}
