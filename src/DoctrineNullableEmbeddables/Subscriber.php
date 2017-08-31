<?php

namespace Fmasa\DoctrineNullableEmbeddables;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;


class Subscriber implements EventSubscriber
{

    /** @var string[] */
    private $embeddablesTree = [];

    /** @var Reader */
    private $reader;


    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

	public function getSubscribedEvents(): array
	{
		return ['postLoad'];
	}

    private function getNullableEmbeddables(
    	ClassMetadata $metadata,
		EntityManager $entityManager,
		$prefix = null
	): array
    {
        if (!isset($this->embeddablesTree[$metadata->getName()])) {
            $nullables = [];
            foreach ($metadata->embeddedClasses as $field => $embeddable) {

                if(strpos($field, ".") !== false) {
                    continue;
                }

                $prefixedField = $prefix !== null ? $prefix . '.' . $field : $field;

                $nullables = array_merge(
                    $nullables,
                    $this->getNullableEmbeddables(
                        $entityManager->getClassMetadata($embeddable['class']),
						$entityManager,
                        $prefixedField
                    )
                );

                $annotation = $this->reader->getPropertyAnnotation(
                    $metadata->getReflectionProperty($field),
                    Nullable::class
                    );

                if ($annotation !== null) {
                    $nullables[] = $prefixedField;
                }
            }
            $this->embeddablesTree[$metadata->getName()] = $nullables;
        }

        return $this->embeddablesTree[$metadata->getName()];
    }

    private function clearEmbeddableIfNecessary(
    	$object,
		string $field,
		EntityManager $entityManager
	)
    {
        if ($object === null || $object instanceof Proxy) {
            return;
        }

        $nested = strpos($field, '.');

        $baseField = $nested === false ? $field : substr($field, 0, $nested);
        $property = $entityManager->getClassMetadata(get_class($object))->getReflectionProperty($baseField);
        $property->setAccessible(true);

        if ($nested === false) {
            if ($this->isEmpty($property->getValue($object))) {
                $property->setValue($object, null);
            }
        } else {
            $this->clearEmbeddableIfNecessary(
            	$property->getValue($object),
				substr($field, $nested + 1),
				$entityManager
			);
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $className = get_class($object);
        $entityManager = $args->getEntityManager();
        $metadata = $entityManager->getClassMetadata($className);

        foreach ($this->getNullableEmbeddables($metadata, $entityManager) as $embeddable) {
            $this->clearEmbeddableIfNecessary($object, $embeddable, $entityManager);
        }
    }

    private function isEmpty($object): bool
    {
        if (empty($object)) {
            return true;
        } elseif (is_numeric($object)) {
            return false;
        } elseif (is_string($object)) {
            return !strlen(trim($object));
        }

        // It's an object or array!
        foreach ((array)$object as $element) {
            if (!$this->isEmpty($element)) {
                return false;
            }
        }

        return true;
    }

}
