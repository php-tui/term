<?php

declare(strict_types=1);

namespace PhpTui\Term\InformationProvider;

use PhpTui\Term\InformationProvider;
use PhpTui\Term\TerminalInformation\Size;
use PhpTui\Term\TerminalInformation;
use PhpTui\Term\WindowsConsole;

final class SizeFromWinProvider implements InformationProvider
{
    private function __construct(private readonly WindowsConsole $windowsConsole)
    {
    }

    public static function new(?WindowsConsole $windowsConsole = null): self
    {
        return new self($windowsConsole ?? WindowsConsole::getInstance());
    }

    public function for(string $classFqn): ?TerminalInformation
    {
        if ($classFqn !== Size::class) {
            return null;
        }

        $out = $this->windowsConsole->getConsoleScreenBufferInfo();

        if (! is_array($out)) {
            return null;
        }

        /**
         * @phpstan-ignore-next-line */
        return $this->parse($out);
    }

    /**
    * @param array{windowSize: array{width: int, height: int}} $out
    */
    private function parse(array $out): Size
    {
        return new Size(max(0, (int) ($out['windowSize']['height'])), max(0, (int) ($out['windowSize']['width'])));
    }
}
