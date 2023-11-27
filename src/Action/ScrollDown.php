<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;
use PhpTui\Term\Event;

final class ScrollDown implements Action
{
    public function __toString(): string
    {
        return 'ScrollDown()';
    }
}
