<?php

use PhpTui\Term\Action;
use PhpTui\Term\Actions;
use PhpTui\Term\RawMode\TestRawMode;
use PhpTui\Term\InformationProvider\ClosureInformationProvider;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PhpTui\Term\Painter\ArrayPainter;
use PhpTui\Term\Terminal;
use PhpTui\Term\TerminalInformation;

require __DIR__ . '/../vendor/autoload.php';

// this example demonstrates the various stub dependencies
// which you can (for example) use in tests
/
$painter = ArrayPainter::new();
$eventProvider = ArrayEventProvider::fromEvents(
    CharKeyEvent::new('c')
);
$infoProvider = ClosureInformationProvider::new(
    function (string $classFqn): TerminalInformation {
        return new class implements TerminalInformation {};
    }
);
$rawMode = new TestRawMode();

$term = Terminal::new(
    painter: $painter,
    infoProvider: $infoProvider,
    eventProvider: $eventProvider,
    rawMode: $rawMode
);
$term->execute(
    Actions::printString('Hello World'),
    Actions::setTitle('Terminal Title'),
);

echo implode("\n", array_map(fn (Action $action) => $action->__toString(), $painter->actions())). "\n";
