<?php
namespace Helmich\EventBroker\Aspect;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


use Helmich\EventBroker\Broker\BrokerInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Aop\JoinPointInterface;


/**
 * Aspect that publishes an event once an method with an "event" annotation has been called.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Helmich\EventBroker
 * @subpackage Aspect
 *
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
     * Publishes an event as soon as a method with an event annotation is called.
     *
     * @param JoinPointInterface $joinPoint The join point.
     * @return void
     *
     * @Flow\After("methodAnnotatedWith(Mw\EventBroker\Annotations\Event)")
     */
    public function publishEventAdvice(JoinPointInterface $joinPoint)
    {
        $event = array_values($joinPoint->getMethodArguments())[0];
        $this->broker->queueEvent($event);
    }

} 