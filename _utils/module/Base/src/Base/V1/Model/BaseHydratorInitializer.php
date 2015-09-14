<?php
namespace Base\V1\Model;

use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DoctrineModule\Stdlib\Hydrator\Filter\PropertyName;


class BaseHydratorInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if (!($instance instanceof DoctrineObject)) {
            return;
        }
        
        $instance->addFilter('hidden', new PropertyName(array('password'), true));
    }
}