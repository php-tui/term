<?php

declare(strict_types=1);

namespace PhpTui\Term\TerminalInformation;

use PhpTui\Term\TerminalInformation;
use Stringable;

final class Size implements TerminalInformation, Stringable
{
    public function __construct(
        /**
         * @var int<0,max>
         */
        public int $lines,
        /**
         * @var int<0,max>
         */
        public int $cols
    ) {
    }

    public function __toString(): string
    {
        return sprintf('%dx%d', $this->cols, $this->lines);
    }
}
