<?php
namespace Mw\EventBroker;


use TYPO3\Flow\Core\Bootstrap;
use TYPO3\Flow\Package\Package as BasePackage;


class Package extends BasePackage
{



    public function boot(Bootstrap $bootstrap)
    {
        parent::boot($bootstrap);

        $dispatcher = $bootstrap->getSignalSlotDispatcher();
        $dispatcher->connect(
            'TYPO3\Flow\Mvc\Dispatcher',
            'afterControllerInvocation',
            'Mw\EventBroker\Broker\BrokerInterface',
            'flush'
        );
    }



}