<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

class MoveCursorLeft implements Action
{
    public function __construct(public readonly int $cols)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorLeft(%d)', $this->cols);
    }
}
