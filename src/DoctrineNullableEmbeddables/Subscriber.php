<?php

namespace Fmasa\DoctrineNullableEmbeddables;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable;
use ReflectionClass;


class Subscriber implements EventSubscriber
{

    /** @var string[] */
    private $embeddablesTree = [];

    /** @var ReflectionClass[] */
    private $reflections = [];

    /** @var Reader */
    private $reader;


    public function getSubscribedEvents(): array
    {
        return ['postLoad'];
    }


    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }


    private function getNullableEmbeddables(
    	ClassMetadata $metadata,
		EntityManager $entityManager,
		$prefix = NULL): array
    {
        if (!isset($this->embeddablesTree[$metadata->getName()])) {
            $nullables = [];
            foreach ($metadata->embeddedClasses as $field => $embeddable) {

                if(strpos($field, ".") !== FALSE) {
                    continue;
                }

                $prefixedField = $prefix !== NULL ? $prefix . '.' . $field : $field;

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

                if ($annotation !== NULL) {
                    $nullables[] = $prefixedField;
                }
            }
            $this->embeddablesTree[$metadata->getName()] = $nullables;
        }

        return $this->embeddablesTree[$metadata->getName()];
    }


    private function getReflection(string $class): ReflectionClass
    {
        if (!isset($this->reflections[$class])) {
            $this->reflections[$class] = new ReflectionClass($class);
        }
        return $this->reflections[$class];
    }


    private function clearEmbeddableIfNecessary($object, string $field)
    {
        if ($object === NULL || $object instanceof Proxy) {
            return;
        }

        $nested = strpos($field, '.');

        $reflection = $this->getReflection(get_class($object));

        $property = $reflection->getProperty($nested === FALSE ? $field : substr($field, 0, $nested));
        $property->setAccessible(TRUE);

        if ($nested === FALSE) {
            if ($this->isEmpty($property->getValue($object))) {
                $property->setValue($object, NULL);
            }
        } else {
            $this->clearEmbeddableIfNecessary($property->getValue($object), substr($field, $nested + 1));
        }
    }


    public function postLoad(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $className = get_class($object);
        $entityManager = $args->getEntityManager();
        $metadata = $entityManager->getClassMetadata($className);

        foreach ($this->getNullableEmbeddables($metadata, $entityManager) as $embeddable) {
            $this->clearEmbeddableIfNecessary($object, $embeddable);
        }
    }


    private function isEmpty($object): bool
    {
        if (empty($object)) {
            return TRUE;
        } elseif (is_numeric($object)) {
            return FALSE;
        } elseif (is_string($object)) {
            return !strlen(trim($object));
        }

        // It's an object or array!
        foreach ((array)$object as $element) {
            if (!$this->isEmpty($element)) {
                return FALSE;
            }
        }

        return TRUE;
    }

}
