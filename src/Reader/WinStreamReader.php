<?php

declare(strict_types=1);

namespace PhpTui\Term\Reader;

use PhpTui\Term\Reader;
use PhpTui\Term\WindowsConsole;

final class WinStreamReader implements Reader
{
    private const WINDOWS_RESIZE_SENTINEL = "\x1B]php-tui-resize\x07";

    // https://learn.microsoft.com/en-us/windows/console/input-record-str
    private const KEY_EVENT = 0x0001;
    private const MOUSE_EVENT = 0x0002;
    private const WINDOW_BUFFER_SIZE_EVENT = 0x0004;
    private const FOCUS_EVENT = 0x0010;

    // https://learn.microsoft.com/en-us/windows/console/mouse-event-record-str
    private const FROM_LEFT_1ST_BUTTON_PRESSED = 0x0001;
    private const RIGHTMOST_BUTTON_PRESSED = 0x0002;
    private const FROM_LEFT_2ND_BUTTON_PRESSED = 0x0004;
    private const MOUSE_MOVED = 0x0001;
    private const MOUSE_WHEELED = 0x0004;
    private const WHEEL_MASK = 0x8001;
    private const WHEEL_EXTEND_MASK = ~0x10000;

    // https://learn.microsoft.com/en-us/windows/console/key-event-record-str
    private const ALT_PRESSED = 0x0001 | 0x0002;
    private const CTRL_PRESSED = 0x0004 | 0x0008;
    private const SHIFT_PRESSED = 0x0010;

    // FN keys - If there are more without ascii representation, we can add them here
    // https://learn.microsoft.com/en-us/windows/win32/inputdev/virtual-key-codes
    private const VK_F1 = 0x70;  // 112
    private const VK_F2 = 0x71;  // 113
    private const VK_F3 = 0x72;  // 114
    private const VK_F4 = 0x73;  // 115
    private const VK_F5 = 0x74;  // 116
    private const VK_F6 = 0x75;  // 117
    private const VK_F7 = 0x76;  // 118
    private const VK_F8 = 0x77;  // 119
    private const VK_F9 = 0x78;  // 120
    private const VK_F10 = 0x79; // 121
    private const VK_F11 = 0x7A; // 122
    private const VK_F12 = 0x7B; // 123
    private const VK_F13 = 0x7C; // 124
    private const VK_F14 = 0x7D; // 125
    private const VK_F15 = 0x7E; // 126
    private const VK_F16 = 0x7F; // 127
    private const VK_F17 = 0x80; // 128
    private const VK_F18 = 0x81; // 129
    private const VK_F19 = 0x82; // 130
    private const VK_F20 = 0x83; // 131
    private const VK_F21 = 0x84; // 132
    private const VK_F22 = 0x85; // 133
    private const VK_F23 = 0x86; // 134
    private const VK_F24 = 0x87; // 135
    private const VK_BACKSPACE = 0x08; // 8
    private const VK_LEFT = 0x25; // 37
    private const VK_UP = 0x26; // 38
    private const VK_RIGHT = 0x27; // 39
    private const VK_DOWN = 0x28; // 40
    private const VK_PRINT = 0x2A; // 42
    private const VK_SCROLL = 0x91; // 145
    private const VK_PAUSE = 0x13; // 19
    private const VK_INSERT = 0x2D; // 45
    private const VK_HOME = 0x24; // 36
    private const VK_PRIOR = 0x21; // 33
    private const VK_DELETE = 0x2E; // 46
    private const VK_END = 0x23; // 35
    private const VK_NEXT = 0x22; // 34

    private bool $pendingNull = false;

    /** @var list<string> */
    private array $pendingInput = [];

    private ?int $pendingHighSurrogate = null;

    private int $lastPressedButton = 0;

    private int $lastModifierState = 0;

    private function __construct(private readonly WindowsConsole $windowsConsole)
    {
    }

    public static function new(?WindowsConsole $windowsConsole = null): self
    {
        return new self($windowsConsole ?? WindowsConsole::getInstance());
    }

    public function read(): ?string
    {
        if ($this->pendingInput !== []) {
            return array_shift($this->pendingInput);
        }

        // We only parse the stream when we return a null.
        // With Key events, we need to set a flag to return null on the next loop to mimic unix behavior.
        // With Mouse events, we need to return null on the next loop,
        // or we are stuck waiting for something else to trigger this.
        // If we have a pending null return, return it and clear the flag
        if ($this->pendingNull) {
            $this->pendingNull = false;

            return null;
        }

        $numEvents = $this->windowsConsole->getNumberOfConsoleInputEvents();

        if ($numEvents < 1) {
            return null;
        }

        foreach ($this->windowsConsole->readConsoleInputRecords(min($numEvents, WindowsConsole::INPUT_RECORD_BATCH_SIZE)) as $input) {
            $this->queueInput($input);
        }

        if ($this->pendingInput !== []) {
            return array_shift($this->pendingInput);
        }

        return null;
    }

    /**
     * @param array{eventType: int, keyEvent?: array{keyDown: bool, repeatCount: int, virtualKeyCode: int, unicodeChar: int, controlKeyState: int}, mouseEvent?: array{mousePosition: array{x: int, y: int}, buttonState: int, controlKeyState: int, eventFlags: int}, windowBufferSizeEvent?: array{size: array{x: int, y: int}}, focusEvent?: array{setFocus: bool}} $input
     */
    private function queueInput(array $input): void
    {
        // TODO: See what other events need to get handled here
        // https://github.com/php-tui/term/blob/main/src/EventParser.php#L73
        switch ($input['eventType']) {
            case self::KEY_EVENT:
                $keyEvent = $input['keyEvent'] ?? null;

                if ($keyEvent === null) {
                    return;
                }

                if ($keyEvent['keyDown']) {
                    $mappedKey = $this->mappedKey($keyEvent['virtualKeyCode']);

                    if ($mappedKey !== null) {
                        $this->queueKey($mappedKey, $keyEvent['repeatCount']);

                        return;
                    }

                    // Prevent sending ctrl/alt/shift keys on their own.
                    if ($keyEvent['unicodeChar'] == 0) {
                        return;
                    }

                    if ($keyEvent['unicodeChar'] === 9) {
                        $modifierCode = $this->modifierCode($keyEvent['controlKeyState']);
                        if ($modifierCode !== 1) {
                            $this->queueKey(sprintf("\x1B[9;%du", $modifierCode), $keyEvent['repeatCount']);

                            return;
                        }
                    }

                    // Alt Char modifiers I don't think linux supports this
                    // if (($keyEvent['controlKeyState'] & self::ALT_PRESSED) !== 0) {
                    //     $codepoint = $this->modifiedCharCodepoint($keyEvent['unicodeChar'], $keyEvent['controlKeyState']);
                    //     if ($codepoint !== null) {
                    //         $this->queueKey(sprintf("\x1B[%d;%du", $codepoint, $this->modifierCode($keyEvent['controlKeyState'])), $keyEvent['repeatCount']);
                    //
                    //         return;
                    //     }
                    // }

                    $key = $this->utf8FromCodeUnit($keyEvent['unicodeChar']);

                    if ($key === null) {
                        return;
                    }

                    $this->queueKey($key, $keyEvent['repeatCount']);
                }
                break;

            case self::MOUSE_EVENT:
                $mouseEvent = $input['mouseEvent'] ?? null;

                if ($mouseEvent === null) {
                    return;
                }

                $mouseInput = $this->calculateSGR($mouseEvent);

                if ($mouseInput !== null) {
                    $this->pendingInput[] = $mouseInput;
                }

                break;
            case self::WINDOW_BUFFER_SIZE_EVENT:
                $this->pendingNull = true;
                $this->pendingInput[] = self::WINDOWS_RESIZE_SENTINEL;

                break;
            case self::FOCUS_EVENT:
                $focusEvent = $input['focusEvent'] ?? null;

                if ($focusEvent === null) {
                    return;
                }

                $this->pendingNull = true;
                $this->pendingInput[] = $focusEvent['setFocus'] ? "\x1B[I" : "\x1B[O";

                break;
            default:
                break;
        }
    }

    /**
     * @param array{mousePosition: array{x: int, y: int}, buttonState: int, controlKeyState: int, eventFlags: int} $mouseEvent
     */
    private function calculateSGR(array $mouseEvent): ?string
    {
        $x = $mouseEvent['mousePosition']['x'] + 1;
        $y = $mouseEvent['mousePosition']['y'] + 1;

        $this->pendingNull = true;

        $button = 0;
        $modifierState = 0;

        if ($mouseEvent['buttonState'] & self::FROM_LEFT_2ND_BUTTON_PRESSED) {
            $button = 1; // Middle button
        } elseif ($mouseEvent['buttonState'] & self::RIGHTMOST_BUTTON_PRESSED) {
            $button = 2; // Right button
        }

        if ($mouseEvent['controlKeyState'] & self::SHIFT_PRESSED) {
            $modifierState |= 4;
        }
        if ($mouseEvent['controlKeyState'] & self::ALT_PRESSED) {
            $modifierState |= 8;
        }
        if ($mouseEvent['controlKeyState'] & self::CTRL_PRESSED) {
            $modifierState |= 16;
        }

        // Handle different event types

        if ($mouseEvent['eventFlags'] === self::MOUSE_MOVED) {
            $buttonState = $mouseEvent['buttonState'];
            if (
                $buttonState &
                    (
                        self::FROM_LEFT_1ST_BUTTON_PRESSED |
                        self::RIGHTMOST_BUTTON_PRESSED |
                        self::FROM_LEFT_2ND_BUTTON_PRESSED
                    )
            ) {
                if ($buttonState & self::FROM_LEFT_1ST_BUTTON_PRESSED) {
                    $button = 32; // Left button drag
                } elseif ($buttonState & self::FROM_LEFT_2ND_BUTTON_PRESSED) {
                    $button = 33; // Middle button drag
                } elseif ($buttonState & self::RIGHTMOST_BUTTON_PRESSED) {
                    $button = 34; // Right button drag
                }

                // Add the stored modifier state from when the drag started
                $button += $this->lastModifierState;

                return sprintf("\x1B[<%d;%d;%dM", $button, $x, $y);
            } else {
                // Movement without buttons
                return sprintf("\x1B[<%d;%d;%dm", 35, $x, $y);
            }
        } elseif ($mouseEvent['eventFlags'] === self::MOUSE_WHEELED) {
            $wheelDelta = ($mouseEvent['buttonState'] >> 16);
            if ($wheelDelta & self::WHEEL_MASK) {
                $wheelDelta |= self::WHEEL_EXTEND_MASK;
            }

            if ($wheelDelta < 0) {
                // Wheel up
                return sprintf("\x1B[<%d;%d;%dM", 65 + $modifierState, $x, $y);
            }

            // Wheel down
            return sprintf("\x1B[<%d;%d;%dM", 64 + $modifierState, $x, $y);
        } elseif ($mouseEvent['eventFlags'] === 0) {
            $button += $modifierState;

            if ($mouseEvent['buttonState'] === 0) {
                // Button release - use lowercase 'm' with the last pressed button and modifiers
                return sprintf("\x1B[<%d;%d;%dm", $this->lastPressedButton, $x, $y);
            }

            // Button press - store both button and modifier state
            $this->lastPressedButton = $button;
            $this->lastModifierState = $modifierState;

            return sprintf("\x1B[<%d;%d;%dM", $button, $x, $y);
        }

        return null;
    }

    private function mappedKey(int $virtualKeyCode): ?string
    {
        return match ($virtualKeyCode) {
            self::VK_F1 => "\x1B[11~",
            self::VK_F2 => "\x1B[12~",
            self::VK_F3 => "\x1B[13~",
            self::VK_F4 => "\x1B[14~",
            self::VK_F5 => "\x1B[15~",
            self::VK_F6 => "\x1B[17~",
            self::VK_F7 => "\x1B[18~",
            self::VK_F8 => "\x1B[19~",
            self::VK_F9 => "\x1B[20~",
            self::VK_F10 => "\x1B[21~",
            self::VK_F11 => "\x1B[23~",
            self::VK_F12 => "\x1B[24~",
            self::VK_F13 => "\x1B[25~",
            self::VK_F14 => "\x1B[26~",
            self::VK_F15 => "\x1B[27~",
            self::VK_F16 => "\x1B[28~",
            self::VK_F17 => "\x1B[29~",
            self::VK_F18 => "\x1B[30~",
            self::VK_F19 => "\x1B[31~",
            self::VK_F20 => "\x1B[32~",
            self::VK_F21 => "\x1B[33~",
            self::VK_F22 => "\x1B[34~",
            self::VK_F23 => "\x1B[35~",
            self::VK_F24 => "\x1B[36~",
            self::VK_BACKSPACE => "\x7F",
            self::VK_LEFT => "\x1B[D",
            self::VK_UP => "\x1B[A",
            self::VK_RIGHT => "\x1B[C",
            self::VK_DOWN => "\x1B[B",
            self::VK_PRINT => "\x1B[32~",
            self::VK_SCROLL => "\x1B[33~",
            self::VK_PAUSE => "\x1B[34~",
            self::VK_INSERT => "\x1B[2~",
            self::VK_HOME => "\x1B[H",
            self::VK_PRIOR => "\x1B[5~",
            self::VK_DELETE => "\x1B[3~",
            self::VK_END => "\x1B[F",
            self::VK_NEXT => "\x1B[6~",
            default => null,
        };
    }

    private function queueKey(string $key, int $repeatCount = 1): void
    {
        if ($repeatCount < 1) {
            return;
        }

        $this->pendingNull = true;

        for ($i = 0; $i < $repeatCount; $i++) {
            $this->pendingInput[] = $key;
        }
    }

    private function modifierCode(int $controlKeyState): int
    {
        $modifierCode = 1;

        if (($controlKeyState & self::SHIFT_PRESSED) !== 0) {
            $modifierCode += 1;
        }
        if (($controlKeyState & self::ALT_PRESSED) !== 0) {
            $modifierCode += 2;
        }
        if (($controlKeyState & self::CTRL_PRESSED) !== 0) {
            $modifierCode += 4;
        }

        return $modifierCode;
    }

    // private function modifiedCharCodepoint(int $unicodeChar, int $controlKeyState): ?int
    // {
    //     if ($unicodeChar >= 32) {
    //         return $unicodeChar;
    //     }
    //
    //     if (($controlKeyState & self::CTRL_PRESSED) === 0) {
    //         return null;
    //     }
    //
    //     if ($unicodeChar >= 1 && $unicodeChar <= 26) {
    //         return $unicodeChar - 1 + (($controlKeyState & self::SHIFT_PRESSED) !== 0 ? ord('A') : ord('a'));
    //     }
    //
    //     if ($unicodeChar >= 28) {
    //         return $unicodeChar - ord("\x1C") + ord('4');
    //     }
    //
    //     return null;
    // }

    // ReadConsoleInputW returns UTF-16 code units, so we need to handle surrogate pairs to convert them to UTF-8 characters.
    private function utf8FromCodeUnit(int $codeUnit): ?string
    {
        if ($codeUnit >= 0xD800 && $codeUnit <= 0xDBFF) {
            $this->pendingHighSurrogate = $codeUnit;

            return null;
        }

        if ($codeUnit >= 0xDC00 && $codeUnit <= 0xDFFF) {
            if ($this->pendingHighSurrogate === null) {
                return null;
            }

            $highSurrogate = $this->pendingHighSurrogate;
            $this->pendingHighSurrogate = null;

            return mb_chr(0x10000 + (($highSurrogate - 0xD800) << 10) + ($codeUnit - 0xDC00), 'UTF-8');
        }

        $this->pendingHighSurrogate = null;

        return mb_chr($codeUnit, 'UTF-8');
    }

}
