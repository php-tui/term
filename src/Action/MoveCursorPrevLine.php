<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

class MoveCursorPrevLine implements Action
{
    public function __construct(public readonly int $nbLines)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorPrevLine(%d)', $this->nbLines);
    }
}
