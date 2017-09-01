<?php

declare(strict_types=1);

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

    private function clearEmbeddablesIfNecessary($object, EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata(get_class($object));

        foreach($metadata->embeddedClasses as $fieldName => $embeddable) {
        	if(strpos($fieldName, '.') !== false) {
        		continue;
			}

			$field = $metadata->getReflectionProperty($fieldName);
			$value = $field->getValue($object);

			if($value === null) {
				continue;
			}

        	if($this->hasNullableAnnotation($field)) {
				$this->clearEmbeddablesIfNecessary(
					$value,
					$entityManager
				);

				if($this->isEmpty($value, $entityManager->getClassMetadata($embeddable['class']))) {
					$field->setValue($object, null);
				}
			}
		}
    }

    public function postLoad(LifecycleEventArgs $args)
    {
    	$object = $args->getObject();

    	if($object instanceof Proxy) {
    		return;
		}

        $this->clearEmbeddablesIfNecessary(
        	$object,
			$args->getEntityManager()
		);
    }

    private function isEmpty($object, ClassMetadata $metadata): bool
    {
    	foreach($metadata->getFieldNames() as $fieldName) {
    		if($metadata->getFieldValue($object, $fieldName) !== null) {
    			return false;
			}
		}

		return true;
    }

    private function hasNullableAnnotation(\ReflectionProperty $property): bool
	{
		return $this->reader->getPropertyAnnotation($property, Nullable::class) !== null;
	}

}
