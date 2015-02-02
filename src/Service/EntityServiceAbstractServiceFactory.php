<?php
/**
 * Polder Knowledge / Entity Service (http://polderknowledge.nl)
 *
 * @link http://developers.polderknowledge.nl/gitlab/polderknowledge/entityservice for the canonical source repository
 * @copyright Copyright (c) 2015-2015 Polder Knowledge (http://www.polderknowledge.nl)
 * @license http://polderknowledge.nl/license/proprietary proprietary
 */

namespace PolderKnowledge\EntityService\Service;

use PolderKnowledge\EntityService\Service\DefaultEntityService;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityServiceAbstractServiceFactory implements AbstractFactoryInterface
{
    const REPOSITORY_SERVICE_KEY = 'EntityRepositoryManager';

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        // Ideally we would check if the class that we want to create is an instance of IdentifiableInterface.
        // Unforunaltey we cannot check if that is the case at this point. The class that we request could expect
        // constructor params meaning that PHP warnings would occur here.

        return true;
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $entityRepositoryManager = $serviceLocator->getServiceLocator()->get(self::REPOSITORY_SERVICE_KEY);

        return new DefaultEntityService($entityRepositoryManager, $requestedName);
    }
}
