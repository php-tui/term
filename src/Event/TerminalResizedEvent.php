<?php

namespace PhpTui\Term\Event;

use PhpTui\Term\Event;

final class TerminalResizedEvent implements Event
{
    public function __toString(): string
    {
        return 'TerminalResized()';
    }
}
