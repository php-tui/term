<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class ScrollDown implements Action
{
    public function __construct(public readonly int $rows = 1)
    {
    }

    public function __toString(): string
    {
        return sprintf('ScrollDown(%d)', $this->rows);
    }
}
