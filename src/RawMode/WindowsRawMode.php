<?php

declare(strict_types=1);

namespace PhpTui\Term\RawMode;

use FFI;
use PhpTui\Term\RawMode;
use RuntimeException;

/**
 * Win32 Console API — see:
 * https://learn.microsoft.com/en-us/windows/console/getstdhandle
 * https://learn.microsoft.com/en-us/windows/console/getconsolemode
 * https://learn.microsoft.com/en-us/windows/console/setconsolemode
 */
final class WindowsRawMode implements RawMode
{
    private const STD_INPUT_HANDLE = 0xFFFFFFF6;
    private const STD_OUTPUT_HANDLE = 0xFFFFFFF5;
    private const ENABLE_LINE_INPUT = 0x0002;
    private const ENABLE_ECHO_INPUT = 0x0004;
    private const ENABLE_PROCESSED_INPUT = 0x0001;
    private const ENABLE_VIRTUAL_TERMINAL_INPUT = 0x0200;
    private const ENABLE_VIRTUAL_TERMINAL_PROCESSING = 0x0004;

    private readonly FFI $kernel32;

    /** @var FFI\CData opaque HANDLE */
    private FFI\CData $stdin;

    /** @var FFI\CData opaque HANDLE */
    private FFI\CData $stdout;

    // null means "not currently enabled" — same role $originalSettings
    // plays in SttyRawMode, just two values instead of one (input mode +
    // output mode are two separate console mode words on Windows).
    private ?int $originalInputMode = null;

    private ?int $originalOutputMode = null;

    private function __construct()
    {
        if (!extension_loaded('ffi')) {
            throw new RuntimeException('The ffi extension is required for WindowsRawMode but is not loaded.');
        }

        $this->kernel32 = FFI::cdef(<<<'CDEF'
            typedef void* HANDLE;
            typedef unsigned long DWORD;
            typedef int BOOL;

            HANDLE GetStdHandle(DWORD nStdHandle);
            BOOL GetConsoleMode(HANDLE hConsoleHandle, DWORD *lpMode);
            BOOL SetConsoleMode(HANDLE hConsoleHandle, DWORD dwMode);
            CDEF, 'kernel32.dll');

        $this->stdin = $this->kernel32->GetStdHandle(self::STD_INPUT_HANDLE); // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
        $this->stdout = $this->kernel32->GetStdHandle(self::STD_OUTPUT_HANDLE); // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)

        if (FFI::isNull($this->stdin) || FFI::isNull($this->stdout)) {
            throw new RuntimeException('GetStdHandle failed — is stdin/stdout a real console?');
        }
    }

    public static function new(): self
    {
        return new self();
    }

    public function enable(): void
    {
        if (null !== $this->originalInputMode) {
            return;
        }

        $inputMode = $this->kernel32->new('DWORD');
        $outputMode = $this->kernel32->new('DWORD');

        if (!$this->kernel32->GetConsoleMode($this->stdin, FFI::addr($inputMode))) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('GetConsoleMode (input) failed.');
        }
        if (!$this->kernel32->GetConsoleMode($this->stdout, FFI::addr($outputMode))) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('GetConsoleMode (output) failed.');
        }

        // Remember the pre-raw-mode values so disable() can restore them —
        // this is the Windows equivalent of SttyRawMode's `stty -g` snapshot.
        $this->originalInputMode = $inputMode->cdata; // @phpstan-ignore property.notFound (FFI\CData::$cdata is a dynamic magic property PHPStan can't type)
        $this->originalOutputMode = $outputMode->cdata; // @phpstan-ignore property.notFound (FFI\CData::$cdata is a dynamic magic property PHPStan can't type)

        $rawInputMode = ($this->originalInputMode | self::ENABLE_VIRTUAL_TERMINAL_INPUT)
            & ~self::ENABLE_LINE_INPUT & ~self::ENABLE_ECHO_INPUT & ~self::ENABLE_PROCESSED_INPUT;
        $rawOutputMode = $this->originalOutputMode | self::ENABLE_VIRTUAL_TERMINAL_PROCESSING;

        if (!$this->kernel32->SetConsoleMode($this->stdin, $rawInputMode)) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('Could not set raw mode (input).');
        }
        if (!$this->kernel32->SetConsoleMode($this->stdout, $rawOutputMode)) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('Could not set raw mode (output).');
        }
    }

    public function disable(): void
    {
        if (null === $this->originalInputMode) {
            return;
        }

        if (!$this->kernel32->SetConsoleMode($this->stdin, $this->originalInputMode)) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('Could not restore from raw mode (input).');
        }
        if (!$this->kernel32->SetConsoleMode($this->stdout, $this->originalOutputMode)) { // @phpstan-ignore method.notFound (FFI::cdef() methods are resolved at runtime, PHPStan can't see them)
            throw new RuntimeException('Could not restore from raw mode (output).');
        }

        $this->originalInputMode = null;
        $this->originalOutputMode = null;
    }

    public function isEnabled(): bool
    {
        return $this->originalInputMode !== null;
    }
}
