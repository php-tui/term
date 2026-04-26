<?php

declare(strict_types=1);

namespace PhpTui\Term;

use FFI;
use RuntimeException;

final class WindowsConsole
{
    public const INPUT_RECORD_BATCH_SIZE = 64;

    // https://learn.microsoft.com/en-us/windows/console/getstdhandle
    private const STD_INPUT_HANDLE = -10;
    private const STD_OUTPUT_HANDLE = -11;

    private static ?self $instance = null;

    /** @var FFI&Kernel32FFI */
    private FFI $ffi;

    private FFI\CData $handleIn;

    private FFI\CData $handleOut;

    private FFI\CData $consoleBufferInfo;

    private FFI\CData $mode;

    private FFI\CData $inputRecordRead;

    private FFI\CData $numEventsRead;

    private FFI\CData $numEventsAvailable;

    private FFI\CData $inputRecordPeek;

    private FFI\CData $numEventsPeek;

    public function __construct()
    {
        if (! extension_loaded('ffi')) {
            throw new RuntimeException('FFI extension is not loaded');
        }

        $this->ffi = self::createKernel32();

        $this->handleIn = $this->ffi->GetStdHandle(self::STD_INPUT_HANDLE);

        if (FFI::isNull($this->handleIn)) {
            throw new RuntimeException('Failed to get console handle');
        }

        $this->handleOut = $this->ffi->GetStdHandle(self::STD_OUTPUT_HANDLE);

        if (FFI::isNull($this->handleOut)) {
            throw new RuntimeException('Failed to get console handle');
        }

        /** @var FFI\CData $consoleBufferInfo */
        $consoleBufferInfo = $this->ffi->new('CONSOLE_SCREEN_BUFFER_INFO');
        $this->consoleBufferInfo = $consoleBufferInfo;

        /** @var FFI\CData $mode */
        $mode = $this->ffi->new('DWORD');
        $this->mode = $mode;

        /** @var FFI\CData $inputRecordRead */
        $inputRecordRead = $this->ffi->new('INPUT_RECORD[' . self::INPUT_RECORD_BATCH_SIZE . ']');
        $this->inputRecordRead = $inputRecordRead;

        /** @var FFI\CData $numEventsRead */
        $numEventsRead = $this->ffi->new('DWORD');

        $this->numEventsRead = $numEventsRead;
        /** @var FFI\CData $numEventsAvailable */
        $numEventsAvailable = $this->ffi->new('DWORD');

        $this->numEventsAvailable = $numEventsAvailable;
        /** @var FFI\CData $inputRecordPeek */
        $inputRecordPeek = $this->ffi->new('INPUT_RECORD[1]');
        $this->inputRecordPeek = $inputRecordPeek;

        /** @var FFI\CData $numEventsPeek */
        $numEventsPeek = $this->ffi->new('DWORD');
        $this->numEventsPeek = $numEventsPeek;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function new(): self
    {
        return new self();
    }

    public function setConsoleMode(int $mode): void
    {
        if (! $this->ffi->SetConsoleMode($this->handleIn, $mode)) {
            throw new RuntimeException('Failed to set console to raw mode');
        }
    }

    public function getConsoleMode(): int
    {
        if (! $this->ffi->GetConsoleMode($this->handleIn, FFI::addr($this->mode))) {
            throw new RuntimeException('Failed to get console mode');
        }

        /** @var int $mode */
        $mode = $this->mode->cdata;

        return $mode;
    }

    public function readConsoleInput(int $length): FFI\CData
    {
        $length = max(1, min($length, self::INPUT_RECORD_BATCH_SIZE));

        if (! $this->ffi->ReadConsoleInputW($this->handleIn, $this->inputRecordRead, $length, FFI::addr($this->numEventsRead))) {
            throw new RuntimeException('Failed to read console input');
        }

        return $this->inputRecordRead;
    }

    /**
     * @return list<array{eventType: int, keyEvent?: array{keyDown: bool, repeatCount: int, virtualKeyCode: int, unicodeChar: int, controlKeyState: int}, mouseEvent?: array{mousePosition: array{x: int, y: int}, buttonState: int, controlKeyState: int, eventFlags: int}, windowBufferSizeEvent?: array{size: array{x: int, y: int}}, focusEvent?: array{setFocus: bool}}>
     */
    public function readConsoleInputRecords(int $length): array
    {
        $inputRecords = $this->readConsoleInput($length);

        /** @var int $numEventsRead */
        $numEventsRead = $this->numEventsRead->cdata;

        $input = [];

        for ($i = 0; $i < $numEventsRead; $i++) {
            $input[] = $this->inputRecordToArray($inputRecords[$i]);
        }

        return $input;
    }

    public function getNumberOfConsoleInputEvents(): int
    {
        if (! $this->ffi->GetNumberOfConsoleInputEvents($this->handleIn, FFI::addr($this->numEventsAvailable))) {
            throw new RuntimeException('Failed to get number of console input events');
        }

        /** @var int $numEventsAvailable */
        $numEventsAvailable = $this->numEventsAvailable->cdata;

        return $numEventsAvailable;
    }

    public function peekConsoleInput(int $length): FFI\CData
    {
        $this->ffi->PeekConsoleInputW($this->handleIn, $this->inputRecordPeek, $length, FFI::addr($this->numEventsPeek));

        return $this->numEventsPeek;
    }

    /**
    * @return array{screenBufferSize: array{x: int, y: int}, cursorPosition: array{x: int, y: int}, windowSize: array{width: int, height: int}, maximumWindowSize: array{x: int, y: int}, attributes: int}
    */
    public function getConsoleScreenBufferInfo(): array
    {
        if (! $this->ffi->GetConsoleScreenBufferInfo($this->handleOut, FFI::addr($this->consoleBufferInfo))) {
            throw new RuntimeException('Failed to get console screen buffer info');
        }

        $info = $this->consoleBufferInfo;

        return [
            'screenBufferSize' => ['x' => $info->dwSize->X, 'y' => $info->dwSize->Y],
            'cursorPosition' => ['x' => $info->dwCursorPosition->X, 'y' => $info->dwCursorPosition->Y],
            'windowSize' => [
                'width' => $info->srWindow->Right - $info->srWindow->Left + 1,
                'height' => $info->srWindow->Bottom - $info->srWindow->Top + 1,
            ],
            'maximumWindowSize' => ['x' => $info->dwMaximumWindowSize->X, 'y' => $info->dwMaximumWindowSize->Y],
            'attributes' => $info->wAttributes,
        ];
    }

    /**
     * @return array{eventType: int, keyEvent?: array{keyDown: bool, repeatCount: int, virtualKeyCode: int, unicodeChar: int, controlKeyState: int}, mouseEvent?: array{mousePosition: array{x: int, y: int}, buttonState: int, controlKeyState: int, eventFlags: int}, windowBufferSizeEvent?: array{size: array{x: int, y: int}}, focusEvent?: array{setFocus: bool}}
     */
    private function inputRecordToArray(FFI\CData $record): array
    {
        $input = [
            'eventType' => (int) $record->EventType,
        ];

        switch ($input['eventType']) {
            case 0x0001:
                $keyEvent = $record->Event->KeyEvent;
                $input['keyEvent'] = [
                    'keyDown' => (bool) $keyEvent->bKeyDown,
                    'repeatCount' => (int) $keyEvent->wRepeatCount,
                    'virtualKeyCode' => (int) $keyEvent->wVirtualKeyCode,
                    'unicodeChar' => (int) $keyEvent->uChar->UnicodeChar,
                    'controlKeyState' => (int) $keyEvent->dwControlKeyState,
                ];
                break;
            case 0x0002:
                $mouseEvent = $record->Event->MouseEvent;
                $input['mouseEvent'] = [
                    'mousePosition' => [
                        'x' => (int) $mouseEvent->dwMousePosition->X,
                        'y' => (int) $mouseEvent->dwMousePosition->Y,
                    ],
                    'buttonState' => (int) $mouseEvent->dwButtonState,
                    'controlKeyState' => (int) $mouseEvent->dwControlKeyState,
                    'eventFlags' => (int) $mouseEvent->dwEventFlags,
                ];
                break;
            case 0x0004:
                $windowBufferSizeEvent = $record->Event->WindowBufferSizeEvent;
                $input['windowBufferSizeEvent'] = [
                    'size' => [
                        'x' => (int) $windowBufferSizeEvent->dwSize->X,
                        'y' => (int) $windowBufferSizeEvent->dwSize->Y,
                    ],
                ];
                break;
            case 0x0010:
                $focusEvent = $record->Event->FocusEvent;
                $input['focusEvent'] = [
                    'setFocus' => (bool) $focusEvent->bSetFocus,
                ];
                break;
        }

        return $input;
    }

    /**
     * @return FFI&Kernel32FFI
     */
    private static function createKernel32(): FFI
    {
        $header = <<<CLang
            // Types
            typedef void* HANDLE;
            typedef unsigned long DWORD;
            typedef short SHORT;
            typedef unsigned short WORD;
            typedef char CHAR;
            typedef int BOOL;
            
            // https://learn.microsoft.com/en-us/windows/console/coord-str
            typedef struct _COORD {
                SHORT X;
                SHORT Y;
            } COORD;
            
            // https://learn.microsoft.com/en-us/windows/console/key-event-record-str
            typedef struct _KEY_EVENT_RECORD {
                int bKeyDown;
                WORD wRepeatCount;
                WORD wVirtualKeyCode;
                WORD wVirtualScanCode;
                union {
                    CHAR AsciiChar;
                    WORD UnicodeChar;
                } uChar;
                DWORD dwControlKeyState;
            } KEY_EVENT_RECORD;
            
            // https://learn.microsoft.com/en-us/windows/console/mouse-event-record-str
            typedef struct _MOUSE_EVENT_RECORD {
                COORD dwMousePosition;
                DWORD dwButtonState;
                DWORD dwControlKeyState;
                DWORD dwEventFlags;
            } MOUSE_EVENT_RECORD;
            
            // https://learn.microsoft.com/en-us/windows/console/focus-event-record-str
            typedef struct _FOCUS_EVENT_RECORD {
                BOOL bSetFocus;
            } FOCUS_EVENT_RECORD;

            // https://learn.microsoft.com/en-us/windows/console/window-buffer-size-record-str
            typedef struct _WINDOW_BUFFER_SIZE_RECORD {
                COORD dwSize;
            } WINDOW_BUFFER_SIZE_RECORD;
            
            // https://learn.microsoft.com/en-us/windows/console/input-record-str
            typedef struct _INPUT_RECORD {
                WORD EventType;
                union {
                    KEY_EVENT_RECORD KeyEvent;
                    MOUSE_EVENT_RECORD MouseEvent;
                    WINDOW_BUFFER_SIZE_RECORD WindowBufferSizeEvent;
                    FOCUS_EVENT_RECORD FocusEvent;
                } Event;
            } INPUT_RECORD;

            // https://learn.microsoft.com/en-us/windows/console/small-rect-str
            typedef struct _SMALL_RECT {
                SHORT Left;
                SHORT Top;
                SHORT Right;
                SHORT Bottom;
            } SMALL_RECT;

            // https://learn.microsoft.com/en-us/windows/console/console-screen-buffer-info-str
            typedef struct _CONSOLE_SCREEN_BUFFER_INFO {
                COORD dwSize;
                COORD dwCursorPosition;
                WORD wAttributes;
                SMALL_RECT srWindow;
                COORD dwMaximumWindowSize;
            } CONSOLE_SCREEN_BUFFER_INFO;
            
            // https://learn.microsoft.com/en-us/windows/console/getstdhandle
            HANDLE GetStdHandle(DWORD nStdHandle);
            // https://learn.microsoft.com/en-us/windows/console/getconsolemode
            BOOL GetConsoleMode(HANDLE hConsoleHandle, DWORD* lpMode);
            // https://learn.microsoft.com/en-us/windows/console/setconsolemode
            BOOL SetConsoleMode(HANDLE hConsoleHandle, DWORD dwMode);
            // https://learn.microsoft.com/en-us/windows/console/getconsolescreenbufferinfo
            BOOL GetConsoleScreenBufferInfo(HANDLE hConsoleOutput, CONSOLE_SCREEN_BUFFER_INFO* lpConsoleScreenBufferInfo);
            // https://learn.microsoft.com/en-us/windows/console/getnumberofconsoleinputevents
            BOOL GetNumberOfConsoleInputEvents(HANDLE hConsoleInput, DWORD* lpcNumberOfEvents);
            // https://learn.microsoft.com/en-us/windows/console/readconsoleinput
            // ReadConsoleInputW (Unicode) and ReadConsoleInputA (ANSI)
            BOOL ReadConsoleInputW(HANDLE hConsoleInput, INPUT_RECORD* lpBuffer, DWORD nLength, DWORD* lpNumberOfEventsRead);
            BOOL ReadConsoleInputA(HANDLE hConsoleInput, INPUT_RECORD* lpBuffer, DWORD nLength, DWORD* lpNumberOfEventsRead);
            // https://learn.microsoft.com/en-us/windows/console/peekconsoleinput
            BOOL PeekConsoleInputW(HANDLE hConsoleInput, INPUT_RECORD* lpBuffer, DWORD nLength, DWORD* lpNumberOfEventsRead);
            BOOL PeekConsoleInputA(HANDLE hConsoleInput, INPUT_RECORD* lpBuffer, DWORD nLength, DWORD* lpNumberOfEventsRead);
            CLang;

        /** @var FFI&Kernel32FFI $ffi */
        $ffi = FFI::cdef($header, 'kernel32.dll');

        return $ffi;
    }
}
