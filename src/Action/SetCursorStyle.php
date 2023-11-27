<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;
use PhpTui\Term\CursorStyle;

final class SetCursorStyle implements Action
{
    public function __construct(public readonly CursorStyle $cursorStyle)
    {
    }

    public function __toString(): string
    {
        return sprintf('SetCursorStyle(%s)', $this->cursorStyle->name);
    }
}
