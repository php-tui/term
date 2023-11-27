<?php

declare(ticks=1);

namespace PhpTui\Term\EventProvider;

use PhpTui\Term\Event;
use PhpTui\Term\EventProvider;
use PhpTui\Term\Event\TerminalResizedEvent;

/**
 * Enables processing of signal events,
 * Currently limited to the terminal resize event.
 */
final class SignalEventProvider implements EventProvider
{
    /**
     * @var Event[]
     */
    private static array $events = [];

    public function __construct()
    {
    }

    public static function registerHandler(): void
    {
        if (!function_exists('pcntl_signal')) {
            return;
        }

        pcntl_signal(SIGWINCH, self::handleResize(...));
    }

    public function next(): ?Event
    {
        while ($event = array_shift(self::$events)) {
            return $event;
        }
        return null;
    }

    /**
     * Register the handler and return a new instance.
     */
    public static function registered(): self
    {
        self::registerHandler();
        return new self();
    }

    private static function handleResize(): void
    {
        self::$events[] = new TerminalResizedEvent();
    }
}
