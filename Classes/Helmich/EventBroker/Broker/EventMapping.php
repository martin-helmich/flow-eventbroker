<?php
namespace Helmich\EventBroker\Broker;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


class EventMapping
{



    // Fuck it PHP, you really fucking backstabbed me with this!
    // Visibility actually HAS to be protected (NOT PRIVATE), because otherwise
    // it will be lost when Flow's proxy subclass is serialized (see [1])!
    //
    //     [1] http://php.net/manual/de/language.oop5.magic.php#76643
    protected $eventMap = [];



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