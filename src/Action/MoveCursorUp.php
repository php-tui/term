<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class MoveCursorUp implements Action
{
    public function __construct(public readonly int $lines)
    {
    }
    public function __toString(): string
    {
        return sprintf('MoveCursorUp(%d)', $this->lines);
    }
}
