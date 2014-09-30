<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


class EventMapping
{



    private $eventMap = [];



    public function addListenerForEvent($eventClassName, $listener)
    {
        if (FALSE === array_key_exists($eventClassName, $this->eventMap))
        {
            $this->eventMap[$eventClassName] = [];
        }
        $this->eventMap[$eventClassName][] = $listener;
    }



    public function getListenersForEvent($eventClassName)
    {
        if (FALSE === array_key_exists($eventClassName, $this->eventMap))
        {
            return [];
        }
        return $this->eventMap[$eventClassName];
    }



}