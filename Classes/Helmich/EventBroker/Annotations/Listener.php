<?php
namespace Helmich\EventBroker\Annotations;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Helmich.EventBroker".   *
 *                                                                        *
 * (C) 2014 Martin Helmich <typo3@martin-helmich.de>                      *
 *                                                                        */


/**
 * Annotation to use for tagging listener methods.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @package    Helmich\EventBroker
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
     * Explicitly request synchronous event dispatching.
     * @var bool
     */
    public $synchronous = FALSE;



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

        if (isset($values['sync']))
        {
            $this->synchronous = (bool)$values['sync'];
        }
        else if (isset($values['async']))
        {
            $this->synchronous = !((bool)$values['async']);
        }
    }

} 