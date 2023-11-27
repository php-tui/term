<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class EnableLineWrap implements Action
{
    public function __construct(public readonly bool $enable)
    {
    }

    public function __toString(): string
    {
        return sprintf('EnableLineWrap(%s)', $this->enable ? 'true' : 'false');
    }
}
