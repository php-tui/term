<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class EnableCursorBlinking implements Action
{
    public function __construct(public readonly bool $enable)
    {
    }
    public function __toString(): string
    {
        return sprintf('EnableCursorBlinking(%s)', $this->enable ? 'true' : 'false');
    }
}
