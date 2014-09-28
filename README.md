Event Broker
============

A simple event dispatching library for TYPO3 Flow.

Installation
------------

The package is available on Packagist. Use the following setup in your composer manifest:

    {
        "require": {
            "helmich/flow-eventbroker": "*"
        }
    }

Examples
--------

### Publishing events

Events are regular methods that are tagged with an `@Event\Event` annotation:

    <?php
    namespace My\Example;

    use Helmich\EventBroker\Annotations as Event;

    class Emitter {
        public function doSomething() {
            // ...
            $this->publishSomeEvent(new SomeEvent("foo"));
        }

        /**
         * @Event\Event
         */
        protected function publishSomeEvent(SomeEvent $event) {

        }
    }

### Subscribing to events

Listeners are also regular methods, that are tagged with an `@Event\Listener` annotation.
The event class to listen for is specified as parameter within the annotation.

    <?php
    namespace My\Example;

    use Helmich\EventBroker\Annotations as Event;

    class Listener {
        /**
         * @Event\Listener("My\Example\SomeEvent")
         */
        public function myListener(SomeEvent $event) {

        }
    }

To-Do
-----

- Add unit test coverage
- Provide mechanisms for asynchronous event processing
