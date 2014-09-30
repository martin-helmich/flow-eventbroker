<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


use Helmich\EventBroker\Annotations\Listener;
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
     * @var array
     */
    private
        $synchronousEventMap = [],
        $asynchronousEventMap = [];



    public function initializeObject()
    {
        $this->synchronousEventMap  = $this->cache->get('DispatcherConfiguration_Synchronous');
        $this->asynchronousEventMap = $this->cache->get('DispatcherConfiguration_Asynchronous');

        if (FALSE === ($this->synchronousEventMap || $this->asynchronousEventMap))
        {
            $this->buildEventMap();

            $this->cache->set('DispatcherConfiguration_Asynchronous', $this->asynchronousEventMap);
            $this->cache->set('DispatcherConfiguration_Synchronous', $this->synchronousEventMap);
        }
    }



    /**
     * Enqueues an arbitrary event.
     *
     * @param mixed $event The event object to publish.
     * @return void
     */
    public function queueEvent($event)
    {
        $this->queue[] = $event;

        $class     = get_class($event);
        $listeners = array_key_exists($class, $this->synchronousEventMap)
            ? $this->synchronousEventMap[$class] : [];

        foreach ($listeners as $listener)
        {
            list($listenerClass, $method) = $listener;

            $listenerInstance = $this->objectManager->get($listenerClass);
            $listenerInstance->{$method}($event);
        }
    }



    /**
     * Publishes queued events.
     *
     * @return void
     */
    public function flush()
    {
        foreach ($this->queue as $event)
        {
            $class     = get_class($event);
            $listeners = array_key_exists($class, $this->asynchronousEventMap)
                ? $this->asynchronousEventMap[$class] : [];

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
     */
    private function buildEventMap()
    {
        $eventMap       = NULL;
        $annotationName = 'Helmich\\EventBroker\\Annotations\\Listener';

        $classes = $this->reflectionService->getClassesContainingMethodsAnnotatedWith($annotationName);
        foreach ($classes as $class)
        {
            $classReflection = new ClassReflection($class);
            /** @var MethodReflection $method */
            foreach ($classReflection->getMethods() as $method)
            {
                if ($this->reflectionService->isMethodAnnotatedWith($class, $method->getName(), $annotationName))
                {
                    /** @var Listener $annotation */
                    $annotation = $this->reflectionService->getMethodAnnotation(
                        $class,
                        $method->getName(),
                        $annotationName
                    );

                    $eventMap &= $this->asynchronousEventMap;
                    $event = $annotation->event;

                    if ($annotation->synchronous)
                    {
                        $eventMap &= $this->synchronousEventMap;
                    }

                    if (FALSE === array_key_exists($event, $eventMap))
                    {
                        $eventMap[$event] = [];
                    }
                    $eventMap[$event][] = [$class, $method->getName()];
                }
            }
        }
    }


}
