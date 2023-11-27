<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class MoveCursorToRow implements Action
{
    public function __construct(public readonly int $row)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorToRow(%d)', $this->row);
    }
}
