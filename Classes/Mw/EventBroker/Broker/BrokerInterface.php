<?php
namespace Mw\EventBroker\Broker;


interface BrokerInterface
{



    public function queueEvent($event);



    public function flush();

}