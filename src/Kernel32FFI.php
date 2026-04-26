<?php

declare(strict_types=1);

namespace PhpTui\Term;

use FFI;

/**
 * @method FFI\CData GetStdHandle(int $nStdHandle)
 * @method bool GetConsoleMode(FFI\CData $hConsoleHandle, FFI\CData $lpMode)
 * @method bool SetConsoleMode(FFI\CData $hConsoleHandle, int $dwMode)
 * @method bool GetConsoleScreenBufferInfo(FFI\CData $hConsoleOutput, FFI\CData $lpConsoleScreenBufferInfo)
 * @method bool GetNumberOfConsoleInputEvents(FFI\CData $hConsoleInput, FFI\CData $lpcNumberOfEvents)
 * @method bool ReadConsoleInputA(FFI\CData $hConsoleInput, FFI\CData $lpBuffer, int $nLength, FFI\CData $lpNumberOfEventsRead)
 * @method bool ReadConsoleInputW(FFI\CData $hConsoleInput, FFI\CData $lpBuffer, int $nLength, FFI\CData $lpNumberOfEventsRead)
 * @method bool PeekConsoleInputA(FFI\CData $hConsoleInput, FFI\CData $lpBuffer, int $nLength, FFI\CData $lpNumberOfEventsRead)
 * @method bool PeekConsoleInputW(FFI\CData $hConsoleInput, FFI\CData $lpBuffer, int $nLength, FFI\CData $lpNumberOfEventsRead)
 */
interface Kernel32FFI
{
}
