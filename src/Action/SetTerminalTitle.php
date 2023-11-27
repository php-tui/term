<?php

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class SetTerminalTitle implements Action
{
    public function __construct(public readonly string $title)
    {
    }

    public function __toString(): string
    {
        return sprintf('SetTerminalTitle("%s")', $this->title);
    }
}
