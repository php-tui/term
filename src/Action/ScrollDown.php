<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Event;

final class ScrollDown implements Event
{
    public function __toString(): string
    {
        return 'ScrollDown()';
    }
}
