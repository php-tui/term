<?php

namespace PhpTui\Term\EventProvider;

use PhpTui\Term\Event;
use PhpTui\Term\EventProvider;

final class AggregateEventProvider implements EventProvider
{
    /**
     * @param list<EventProvider> $providers
     */
    public function __construct(private array $providers)
    {
    }

    public function next(): ?Event
    {
        foreach ($this->providers as $provider) {
            $next = $provider->next();
            if (null !== $next) {
                return $next;
            }
        }

        return null;
    }
}
