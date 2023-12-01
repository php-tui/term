<?php

use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\Terminal;

require __DIR__ . '/../vendor/autoload.php';

// this example will enable raw mode and mouse capture and echo
// all events to the terminal.

$terminal = Terminal::new();
$terminal->enableRawMode();
$terminal->execute(Actions::printString('Entering event loop, press ESC to exit'));
$terminal->execute(Actions::moveCursorNextLine());
$terminal->execute(Actions::enableMouseCapture());

try {
    // enter the event loop
    eventLoop($terminal);
} finally {
    // restore the terminal to it's previous state
    $terminal->execute(Actions::disableMouseCapture());
    $terminal->disableRawMode();
}

function eventLoop(Terminal $terminal): void
{
    // start the loop!
    while (true) {

        // drain any events from the event buffer and process them
        while ($event = $terminal->events()->next()) {

            // note we could just as easily do `echo "foo\n";` here but for
            // the sake of the example...
            $terminal->queue(Actions::printString($event->__toString()));
            $terminal->queue(Actions::moveCursorNextLine());

            // flush the buffer. note we could have also used
            // `$terminal->execute(...)` to write the action immediately.
            $terminal->flush();

            // events can be of different types containing different information
            if ($event instanceof CodedKeyEvent) {
                if ($event->code === KeyCode::Esc) {
                    return;
                }
            }

            // most events also have modifiers so you can see if the event happened
            // with a key modifier such as CONTROL or ALT
            if ($event instanceof CharKeyEvent) {
                if ($event->char === 'c' && $event->modifiers === KeyModifiers::CONTROL) {
                    return;
                }
            }
        }
        usleep(10000);
    }
}
