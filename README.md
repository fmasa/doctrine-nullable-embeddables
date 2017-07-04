# Doctrine nullable embeddables

## Installation
The best way to install fmasa/doctrine-yaml-annotations is using [Composer](https://getcomposer.org/):

    $ composer require fmasa/doctrine-nullable-embeddables

There are several conditions that has to be met:
- Property with nullable embeddable must use `Fmasa\DoctrineNullableEmbeddables\Annotations\Nullable` annotation.
- Every property in nullable embeddable must be nullable (or must use `Nullable` annotation if it's embeddable)

Now all you have to do is register `Fmasa\DoctrineNullableEmbeddables\Subscriber` and you have nullable embeddables working:
```php
/* @var $evm Doctrine\Common\EventManager */
/* @var $annotationReader Doctrine\Common\Annotations\Reader */
/* @var $em Doctrine\ORM\EntityManager */

$evm->addEventSubscriber(new Fmasa\DoctrineNullableEmbeddables\Subscriber($em, $annorationReader));
```

### But I'm using YAML for mapping!
See [fmasa/doctrine-yaml-annotations](https://github.com/fmasa/doctrine-yaml-annotations) to make extensions like this work with YAML mapping.
