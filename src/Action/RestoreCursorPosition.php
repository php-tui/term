<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

class RestoreCursorPosition implements Action
{
    public function __toString(): string
    {
        return 'RestoreCursorPosition()';
    }
}
