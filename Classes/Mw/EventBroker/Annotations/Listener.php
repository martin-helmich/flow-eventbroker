<?php
namespace Mw\EventBroker\Annotations;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Mw.EventBroker".        *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


/**
 * Annotation to use for tagging listener methods.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Mw\EventBroker
 * @subpackage Annotations
 *
 * @api
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Listener
{



    /**
     * The event classname to listen on.
     * @var string
     */
    public $event;



    /**
     * Constructs a new annotation.
     *
     * @param array $values Annotation parameters.
     */
    public function __construct(array $values)
    {
        if (!isset($values['value']))
        {
            throw new \InvalidArgumentException('Missing event class name!');
        }
        $this->event = $values['value'];
    }

} 