<?php

use PhpTui\Term\Actions;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\KeyCode;
use PhpTui\Term\KeyModifiers;
use PhpTui\Term\Terminal;

require __DIR__ . '/../vendor/autoload.php';

$terminal = Terminal::new();
echo 'Entering raw mode...' . "\n";

$terminal->enableRawMode();
$terminal->execute(Actions::printString('Entering event loop, press ESC to exit'));
$terminal->execute(Actions::moveCursorNextLine());
$terminal->execute(Actions::enableMouseCapture());
try {
    eventLoop($terminal);
} finally {
    $terminal->execute(Actions::disableMouseCapture());
    $terminal->disableRawMode();
}

function eventLoop(Terminal $terminal): void
{
    while (true) {
        while ($event = $terminal->events()->next()) {
            $terminal->queue(Actions::printString($event->__toString()));
            $terminal->queue(Actions::moveCursorNextLine());
            $terminal->flush();
            if ($event instanceof CodedKeyEvent) {
                if ($event->code === KeyCode::Esc) {
                    return;
                }
            }
            if ($event instanceof CharKeyEvent) {
                if ($event->char === 'c' && $event->modifiers === KeyModifiers::CONTROL) {
                    $terminal->disableRawMode();
                    return;
                }
            }
        }
        usleep(10000);
    }
}
