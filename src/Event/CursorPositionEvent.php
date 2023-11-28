<?php

declare(strict_types=1);

namespace PhpTui\Term\Event;

use PhpTui\Term\Event;

final class CursorPositionEvent implements Event
{
    public function __construct(
        /**
         * @var int<0,max>
         */
        public readonly int $x,
        /**
         * @var int<0,max>
         */
        public readonly int $y
    ) {
    }

    public function __toString(): string
    {
        return sprintf('CursorPosition(%d, %d)', $this->x, $this->y);
    }
}
