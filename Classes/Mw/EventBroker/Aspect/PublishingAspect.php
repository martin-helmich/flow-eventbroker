<?php
namespace Mw\EventBroker\Aspect;


use Mw\EventBroker\Broker\BrokerInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * @Flow\Aspect
 */
class PublishingAspect
{



    /**
     * @var BrokerInterface
     * @Flow\Inject
     */
    protected $broker;



    /**
     * @param JoinPointInterface $joinPoint
     * @Flow\After("methodAnnotatedWith(Mw\EventBroker\Annotations\Event)")
     */
    public function publishEventAdvice(JoinPointInterface $joinPoint)
    {
        $event = array_values($joinPoint->getMethodArguments())[0];
        $this->broker->queueEvent($event);
    }

} 