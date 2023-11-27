<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class MoveCursorToColumn implements Action
{
    public function __construct(public readonly int $col)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorToColumn(%d)', $this->col);
    }
}
