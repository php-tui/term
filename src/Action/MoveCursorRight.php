<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class MoveCursorRight implements Action
{
    public function __construct(public readonly int $cols)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorRight(%d)', $this->cols);
    }
}
