<?php
namespace Mw\EventBroker\Annotations;


/**
 * @Annotation
 * @Target("METHOD")
 */
final class Listener
{



    public $event;



    public function __construct(array $values)
    {
        if (!isset($values['value']))
        {
            throw new \InvalidArgumentException('Missing event class name!');
        }
        $this->event = $values['value'];
    }

} 