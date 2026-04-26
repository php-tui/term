<?php

declare(strict_types=1);

namespace PhpTui\Term\InformationProvider;

use PhpTui\Term\ProcessRunner;
use PhpTui\Term\Terminal;

final class SizeFromProvider
{
    public static function new(?ProcessRunner $processRunner = null): SizeFromWinProvider|SizeFromSttyProvider
    {
        return Terminal::isWindows() ? SizeFromWinProvider::new() : SizeFromSttyProvider::new($processRunner);
    }
}
