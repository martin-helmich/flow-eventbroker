<?php
namespace Helmich\EventBroker\Broker;


/**
 * Interface definition for a message broker.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Helmich\EventBroker
 * @subpackage Annotations
 *
 * @api
 */
interface BrokerInterface
{



    /**
     * Enqueues an arbitrary event.
     *
     * @param mixed $event The event object to publish.
     * @return void
     */
    public function queueEvent($event);



    /**
     * Publishes queued events.
     *
     * @return void
     */
    public function flush();

}