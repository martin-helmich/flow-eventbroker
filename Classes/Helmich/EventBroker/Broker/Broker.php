<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Reflection\ClassReflection;
use TYPO3\Flow\Reflection\MethodReflection;
use TYPO3\Flow\Reflection\ReflectionService;


/**
 * Broker implementation that queues messages locally and publishes them on request.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Helmich\EventBroker
 * @subpackage Broker
 *
 * @Flow\Scope("singleton")
 */
class Broker implements BrokerInterface
{



    /**
     * @var ReflectionService
     * @Flow\Inject(lazy=true)
     */
    protected $reflectionService;


    /**
     * @var ObjectManager
     * @Flow\Inject(lazy=true)
     */
    protected $objectManager;


    /**
     * @var \TYPO3\Flow\Cache\Frontend\VariableFrontend
     * @Flow\Inject
     */
    protected $cache;


    /**
     * @var array
     */
    private $queue = [];



    /**
     * Enqueues an arbitrary event.
     *
     * @param mixed $event The event object to publish.
     * @return void
     */
    public function queueEvent($event)
    {
        $this->queue[] = $event;
    }



    /**
     * Publishes queued events.
     *
     * @return void
     */
    public function flush()
    {
        if (FALSE === ($eventMap = $this->cache->get('DispatcherConfiguration')))
        {
            $eventMap = $this->buildEventMap();
            $this->cache->set('DispatcherConfiguration', $eventMap);
        }

        foreach ($this->queue as $event)
        {
            $class     = get_class($event);
            $listeners = $eventMap[$class];

            foreach ($listeners as $listener)
            {
                list($listenerClass, $method) = $listener;

                $listenerInstance = $this->objectManager->get($listenerClass);
                $listenerInstance->{$method}($event);
            }
        }
    }



    /**
     * Builds the event dispatching configuration.
     *
     * @return array The event dispatching configuration.
     */
    private function buildEventMap()
    {
        $eventMap = [];

        $annotationName = 'Helmich\\EventBroker\\Annotations\\Listener';
        $classes        = $this->reflectionService->getClassesContainingMethodsAnnotatedWith($annotationName);
        foreach ($classes as $class)
        {
            $classReflection = new ClassReflection($class);
            /** @var MethodReflection $method */
            foreach ($classReflection->getMethods() as $method)
            {
                if ($this->reflectionService->isMethodAnnotatedWith($class, $method->getName(), $annotationName))
                {
                    $annotation = $this->reflectionService->getMethodAnnotation(
                        $class,
                        $method->getName(),
                        $annotationName
                    );

                    $event = $annotation->event;

                    if (FALSE === array_key_exists($event, $eventMap))
                    {
                        $eventMap[$event] = [];
                    }
                    $eventMap[$event][] = [$class, $method->getName()];
                }
            }
        }
        return $eventMap;
    }


}
