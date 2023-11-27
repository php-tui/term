<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class ScrollUp implements Action
{
    public function __toString(): string
    {
        return 'ScrollUp()';
    }
}
