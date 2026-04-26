<?php

declare(strict_types=1);

namespace PhpTui\Term\Reader;

use PhpTui\Term\Terminal;

final class StreamReader
{
    public static function new(): WinStreamReader|UnixStreamReader
    {
        return (Terminal::isWindows() ? WinStreamReader::new() : UnixStreamReader::new());
    }
}
