<?php

namespace Fmasa\DoctrineNullableEmbeddables;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use PHPUnit\Framework\TestCase;


class NullableEmbeddablesTest extends TestCase
{

	/** @var EntityManager */
	private $entityManager;

	/** @var SchemaTool */
	private $schema;


	public function setUp()
	{
		$config = Setup::createConfiguration(TRUE);
		$driver = $config->newDefaultAnnotationDriver([__DIR__ . "/stubs"], FALSE);
		$annorationReader = $driver->getReader();
		$config->setMetadataDriverImpl($driver);
		AnnotationRegistry::registerFile(__DIR__ . '/../src/DoctrineNullableEmbeddables/Annotations/Nullable.php');

		$connection = [
			'driver' => 'pdo_sqlite',
			'path' => ':memory:',
		];

		$this->entityManager = $em = EntityManager::create($connection, $config);
		$em->getEventManager()->addEventSubscriber(new Subscriber($em, $annorationReader));
		$this->schema = new SchemaTool($em);
		$this->schema->dropDatabase();
	}


	public function testSimpleNullEmbeddable()
	{
		$this->generateSchema([\SimpleEntity::class]);
		$this->entityManager->persist(new \SimpleEntity(NULL));
		$this->entityManager->flush();
		$this->entityManager->clear();

		/* @var $entity \SimpleEntity */
		$entity = $this->entityManager->find(\SimpleEntity::class, 1);

		$this->assertNull($entity->getEmbeddable());
	}


	public function testSimpleNotNullEmbeddable()
	{
		$this->generateSchema([\SimpleEntity::class]);

		$embeddable = new \Embeddable("string", 0);
		$this->entityManager->persist(new \SimpleEntity($embeddable));
		$this->entityManager->flush();
		$this->entityManager->clear();

		/* @var $entity \SimpleEntity */
		$entity = $this->entityManager->find(\SimpleEntity::class, 1);

		$this->assertEquals($embeddable, $entity->getEmbeddable());
	}


	public function testNestedNullEmbeddable()
	{
		$this->generateSchema([\EntityWithNestedEmbeddable::class]);

		$this->entityManager->persist(new \EntityWithNestedEmbeddable());
		$this->entityManager->flush();
		$this->entityManager->clear();

		/* @var $entity \EntityWithNestedEmbeddable */
		$entity = $this->entityManager->find(\EntityWithNestedEmbeddable::class, 1);

		$this->assertNull($entity->getEmbeddable());
	}


	public function testNestedNotNullEmbeddable()
	{
		$this->generateSchema([\EntityWithNestedEmbeddable::class]);

		$embeddable = new \NestedEmbeddable('string', new \Embeddable('string', 20));
		$this->entityManager->persist(new \EntityWithNestedEmbeddable($embeddable));
		$this->entityManager->flush();
		$this->entityManager->clear();

		/* @var $entity \EntityWithNestedEmbeddable */
		$entity = $this->entityManager->find(\EntityWithNestedEmbeddable::class, 1);

		$this->assertEquals($embeddable, $entity->getEmbeddable());
	}


	/**
	 * @param string[] $entities
	 */
	private function generateSchema(array $entities)
	{
		$metadata = [];
		foreach($entities as $entity) {
			$metadata[] = $this->entityManager->getClassMetadata($entity);
		}

		$this->schema->createSchema($metadata);
	}

}
