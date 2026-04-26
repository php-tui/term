<?php

declare(strict_types=1);

namespace PhpTui\Term\RawMode;

use PhpTui\Term\ProcessRunner;
use PhpTui\Term\Terminal;

final class RawMode
{
    public static function new(?ProcessRunner $processRunner = null): WinRawMode|SttyRawMode
    {
        return (Terminal::isWindows() ? WinRawMode::new() : SttyRawMode::new($processRunner));
    }
}
