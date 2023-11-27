<?php

declare(strict_types=1);

namespace PhpTui\Term\Action;

use PhpTui\Term\Action;

final class Reset implements Action
{
    public function __toString(): string
    {
        return 'Reset()';
    }
}
