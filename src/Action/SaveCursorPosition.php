<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

class SaveCursorPosition implements Action
{
    public function __toString(): string
    {
        return 'SaveCursorPosition()';
    }
}
