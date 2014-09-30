<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


/**
 * Container class for mapping events to listeners.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Helmich\EventBroker
 * @subpackage Broker
 *
 * @package    Helmich\EventBroker\Broker
 */
class EventMapping
{



    // Fuck it PHP, you really fucking backstabbed me with this!
    // Visibility actually HAS to be protected (NOT PRIVATE), because otherwise
    // it will be lost when Flow's proxy subclass is serialized (see [1])!
    //
    //     [1] http://php.net/manual/de/language.oop5.magic.php#76643
    /**
     * Map of events to listeners.
     * @var array
     */
    protected $eventMap = [];



    /**
     * Adds a new listener for an event.
     *
     * @param string   $eventClassName The event class name.
     * @param callable $listener       Any kind of callable.
     * @return void
     */
    public function addListenerForEvent($eventClassName, $listener)
    {
        if (FALSE === array_key_exists($eventClassName, $this->eventMap))
        {
            $this->eventMap[$eventClassName] = [];
        }
        $this->eventMap[$eventClassName][] = $listener;
    }



    /**
     * Gets all listeners for an event.
     *
     * @param string $eventClassName The event class name.
     * @return array An array of callables.
     */
    public function getListenersForEvent($eventClassName)
    {
        if (FALSE === array_key_exists($eventClassName, $this->eventMap))
        {
            return [];
        }
        return $this->eventMap[$eventClassName];
    }



}