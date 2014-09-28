<?php
namespace Mw\EventBroker\Broker;


use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManager;
use TYPO3\Flow\Reflection\ClassReflection;
use TYPO3\Flow\Reflection\MethodReflection;
use TYPO3\Flow\Reflection\ReflectionService;


/**
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
     */
    protected $cache;


    private $queue = [];



    public function queueEvent($event)
    {
        $this->queue[] = $event;
    }



    public function flush()
    {
        $eventMap = [];

        $annotation = 'Mw\\EventBroker\\Annotations\\Listener';
        $classes    = $this->reflectionService->getClassesContainingMethodsAnnotatedWith($annotation);
        foreach ($classes as $class)
        {
            $classReflection = new ClassReflection($class);
            /** @var MethodReflection $method */
            foreach ($classReflection->getMethods() as $method)
            {
                if ($this->reflectionService->isMethodAnnotatedWith($class, $method->getName(), $annotation))
                {
                    $annotation = $this->reflectionService->getMethodAnnotation(
                        $class,
                        $method->getName(),
                        $annotation
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

        foreach ($this->queue as $event)
        {
            $class     = get_class($event);
            $listeners = $eventMap[$class];

            foreach ($listeners as $listener)
            {
                list($class, $method) = $listener;

                $listenerInstance = $this->objectManager->get($class);
                $listenerInstance->{$method}($event);
            }
        }
    }


}