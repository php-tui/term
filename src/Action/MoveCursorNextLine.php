<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class MoveCursorNextLine implements Action
{
    public function __construct(public readonly int $nbLines)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorNextLine(%d)', $this->nbLines);
    }
}
