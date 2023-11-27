<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

class MoveCursorToColumn implements Action
{
    public function __construct(public readonly int $column)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorToColumn(%d)', $this->column);
    }
}
