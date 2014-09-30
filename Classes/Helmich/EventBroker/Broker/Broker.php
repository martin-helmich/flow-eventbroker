<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


use Helmich\EventBroker\Annotations\Listener;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cache\Frontend\FrontendInterface;
use TYPO3\Flow\Object\Exception\UnknownObjectException;
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
     * @var FrontendInterface
     * @Flow\Inject
     */
    protected $cache;


    /**
     * @var \SplQueue
     */
    private $queue = [];


    /**
     * @var EventMapping
     */
    private
        $synchronousEventMap,
        $asynchronousEventMap;



    /**
     * Initializes the broker. Loads the dispatching configuration from cache or builds it.
     */
    public function initializeObject()
    {
        $this->queue = new \SplQueue();

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
        $this->queue->enqueue($event);

        $class = get_class($event);
        foreach ($this->synchronousEventMap->getListenersForEvent($class) as $listener)
        {
            $this->invokeListener($listener, $event);
        }
    }



    /**
     * Publishes queued events.
     *
     * @return void
     */
    public function flush()
    {
        $this->queue->setIteratorMode(\SplQueue::IT_MODE_DELETE);

        foreach ($this->queue as $event)
        {
            $class = get_class($event);

            foreach ($this->asynchronousEventMap->getListenersForEvent($class) as $listener)
            {
                $this->invokeListener($listener, $event);
            }
        }
    }



    /**
     * Invokes a listener for an event.
     *
     * @param callable $listener Any type of callable.
     * @param object   $event    The event object.
     * @return void
     *
     * @throws UnknownObjectException May be thrown when the listener class cannot be instantiated.
     */
    private function invokeListener($listener, $event)
    {
        if (is_array($listener))
        {
            list($listenerClass, $method) = $listener;

            $listenerInstance = $this->objectManager->get($listenerClass);
            $listenerInstance->{$method}($event);
        }
        else
        {
            call_user_func($listener, $event);
        }
    }



    /**
     * Builds the event dispatching configuration.
     */
    private function buildEventMap()
    {
        $this->synchronousEventMap  = new EventMapping();
        $this->asynchronousEventMap = new EventMapping();

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

                    $event = $annotation->event;
                    $this
                        ->getEventMap($annotation->synchronous)
                        ->addListenerForEvent($event, [$class, $method->getName()]);
                }
            }
        }
    }



    private function getEventMap($synchronous)
    {
        return $synchronous ? $this->synchronousEventMap : $this->asynchronousEventMap;
    }


}
