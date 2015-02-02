# PolderKnowledge / EntityService

Minimum PHP version: 5.5.0

## Introduction

This library provides functionality to work with entities. An entity is the representation of an object that usually 
can be retrieved from- and stored on a storage device (e.g. database, webservice, file system). This library makes it
possible to quickly access a manager class without the need of creating the repositories.

## Installation

### Composer

```
"require": {
    "polderknowledge/entityservice": "1.0.*"
}
```

### Tests

This library contains unit tests and we aim for a 100% code coverage. Run the unit tests from the root of the project.
Make sure to use PHPUnit from the vendor library. The configuration is read from phpunit.xml.dist

```
vendor/bin/phpunit
```

### Zend Framework 2

In order to make use of the EntityServiceManager, you need to configure it. Make sure to add an entry to the
service_listener_options and also register the entity service in the service manager. Add the following to 
`config/application.config.php`.

```
'service_manager' => array(
    'invokables' => array(
        'EntityRepositoryManager' => 'PolderKnowledge\EntityService\Service\EntityRepositoryManager',
        'EntityServiceManager' => 'PolderKnowledge\EntityService\Service\EntityServiceManager',
    ),
),
'service_listener_options' => array(
	array(
		'service_manager' => 'EntityServiceManager',
		'config_key'      => 'entity_service_manager',
		'interface'       => '',
		'method'          => '',
	),
),
```

Also make sure you configure the entity repository manager in a module.config.php:

```
'entity_repository_manager' => array(
    'abstract_factories' => array(
        'PolderKnowledge\EntityService\Service\DoctrineRepositoryAbstractFactory',
    ),
),
```


## Repositories

### DoctrineORMRepository

This library has a Doctrine ORM Repository which makes it possible to store and retrieve entities from a database
via Doctrine ORM. This library provides an AbstractServiceFactory that can be used to fall back on.

**NOTE:** This library does not require Doctrine ORM, make sure to add the library to your `composer.json`.