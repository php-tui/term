<?php

declare(strict_types=1);

namespace PhpTui\Term\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Action\AlternateScreenEnable;
use PhpTui\Term\Action\Clear;
use PhpTui\Term\Action\CursorShow;
use PhpTui\Term\Action\EnableCursorBlinking;
use PhpTui\Term\Action\EnableLineWrap;
use PhpTui\Term\Action\EnableMouseCapture;
use PhpTui\Term\Action\MoveCursor;
use PhpTui\Term\Action\MoveCursorDown;
use PhpTui\Term\Action\MoveCursorLeft;
use PhpTui\Term\Action\MoveCursorNextLine;
use PhpTui\Term\Action\MoveCursorPrevLine;
use PhpTui\Term\Action\MoveCursorRight;
use PhpTui\Term\Action\MoveCursorToColumn;
use PhpTui\Term\Action\MoveCursorToRow;
use PhpTui\Term\Action\MoveCursorUp;
use PhpTui\Term\Action\PrintString;
use PhpTui\Term\Action\RequestCursorPosition;
use PhpTui\Term\Action\Reset;
use PhpTui\Term\Action\RestoreCursorPosition;
use PhpTui\Term\Action\SaveCursorPosition;
use PhpTui\Term\Action\ScrollDown;
use PhpTui\Term\Action\ScrollUp;
use PhpTui\Term\Action\SetBackgroundColor;
use PhpTui\Term\Action\SetCursorStyle;
use PhpTui\Term\Action\SetForegroundColor;
use PhpTui\Term\Action\SetModifier;
use PhpTui\Term\Action\SetRgbBackgroundColor;
use PhpTui\Term\Action\SetRgbForegroundColor;
use PhpTui\Term\Action\SetTerminalTitle;
use PhpTui\Term\Attribute;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\CursorStyle;
use PhpTui\Term\Painter;
use PhpTui\Term\Writer;
use RuntimeException;

final class AnsiPainter implements Painter
{
    public function __construct(private readonly Writer $writer)
    {
    }

    public static function new(Writer $writer): self
    {
        return new self($writer);
    }

    public function paint(array $actions): void
    {
        foreach ($actions as $action) {
            $this->drawCommand($action);
        }
    }

    private function drawCommand(Action $action): void
    {
        if ($action instanceof PrintString) {
            $this->writer->write($action->string);

            return;
        }
        if ($action instanceof SetForegroundColor && $action->color === Colors::Reset) {
            $this->writer->write($this->csi('39m'));

            return;
        }
        if ($action instanceof SetBackgroundColor && $action->color === Colors::Reset) {
            $this->writer->write($this->csi('49m'));

            return;
        }

        if ($action instanceof EnableMouseCapture) {
            $this->writer->write(implode('', array_map(fn (string $code): string => $this->csi($code), $action->enable ? [
                // Normal tracking: Send mouse X & Y on button press and release
                '?1000h',
                // Button-event tracking: Report button motion events (dragging)
                '?1002h',
                // Any-event tracking: Report all motion events
                '?1003h',
                // RXVT mouse mode: Allows mouse coordinates of >223
                '?1015h',
                // SGR mouse mode: Allows mouse coordinates of >223, preferred over RXVT mode
                '?1006h',
            ] : [
                // same as above but reversed
                '?1006l',
                '?1015l',
                '?1003l',
                '?1002l',
                '?1000l',
            ])));

            return;
        }

        if ($action instanceof SaveCursorPosition) {
            $this->writer->write("\x1B7");
            return;
        }
        if ($action instanceof RestoreCursorPosition) {
            $this->writer->write("\x1B8");
            return;
        }

        if ($action instanceof SetTerminalTitle) {
            $this->writer->write($this->osc(sprintf("0;%s\x07", $action->title)));
            return;
        }

        if ($action instanceof SetCursorStyle) {
            $this->writer->write(sprintf("\x1b[%d q", match($action->cursorStyle) {
                CursorStyle::DefaultUserShape => 0,
                CursorStyle::BlinkingBlock => 1,
                CursorStyle::SteadyBlock => 2,
                CursorStyle::BlinkingUnderScore => 3,
                CursorStyle::SteadyUnderScore => 4,
                CursorStyle::BlinkingBar => 5,
                CursorStyle::SteadyBar => 6,

            }));
            return;
        }

        $this->writer->write($this->csi(match (true) {
            $action instanceof SetForegroundColor => sprintf('%dm', $this->colorIndex($action->color, false)),
            $action instanceof SetBackgroundColor => sprintf('%dm', $this->colorIndex($action->color, true)),
            $action instanceof SetRgbBackgroundColor => sprintf('48;2;%d;%d;%dm', $action->r, $action->g, $action->b),
            $action instanceof SetRgbForegroundColor => sprintf('38;2;%d;%d;%dm', $action->r, $action->g, $action->b),
            $action instanceof CursorShow => sprintf('?25%s', $action->show ? 'h' : 'l'),
            $action instanceof AlternateScreenEnable => sprintf('?1049%s', $action->enable ? 'h' : 'l'),
            $action instanceof MoveCursor => sprintf('%d;%dH', $action->line, $action->col),
            $action instanceof Reset => '0m',
            $action instanceof ScrollUp => sprintf('%dS', $action->rows),
            $action instanceof ScrollDown => sprintf('%dT', $action->rows),
            $action instanceof EnableLineWrap => $action->enable ? '?7h' : '?7l',
            $action instanceof Clear => match ($action->clearType) {
                ClearType::All => '2J',
                ClearType::Purge => '3J',
                ClearType::FromCursorDown => 'J',
                ClearType::FromCursorUp => '1J',
                ClearType::CurrentLine => '2K',
                ClearType::UntilNewLine => 'K',
            },
            $action instanceof SetModifier => $action->enable ?
                sprintf('%dm', $this->modifierOnIndex($action->modifier)) :
                sprintf('%dm', $this->modifierOffIndex($action->modifier)),
            $action instanceof RequestCursorPosition => '6n',
            $action instanceof MoveCursorNextLine => sprintf('%dE', $action->nbLines),
            $action instanceof MoveCursorPrevLine => sprintf('%dF', $action->nbLines),
            $action instanceof MoveCursorToColumn => sprintf('%dG', $action->col + 1),
            $action instanceof MoveCursorToRow => sprintf('%dd', $action->row + 1),
            $action instanceof MoveCursorUp => sprintf('%dA', $action->lines),
            $action instanceof MoveCursorRight => sprintf('%dC', $action->cols),
            $action instanceof MoveCursorDown => sprintf('%dB', $action->lines),
            $action instanceof MoveCursorLeft => sprintf('%dD', $action->cols),
            $action instanceof EnableCursorBlinking => $action->enable ? '?12h' : '?12l',
            default => throw new RuntimeException(sprintf(
                'Do not know how to handle action: %s',
                $action::class
            ))
        }));
    }

    private function colorIndex(Colors $termColor, bool $background): int
    {
        $offset = $background ? 10 : 0;

        return match ($termColor) {
            Colors::Black => 30,
            Colors::Red => 31,
            Colors::Green => 32,
            Colors::Yellow => 33,
            Colors::Blue => 34,
            Colors::Magenta => 35,
            Colors::Cyan => 36,
            Colors::Gray => 37,
            Colors::DarkGray => 90,
            Colors::LightRed => 91,
            Colors::LightGreen => 92,
            Colors::LightYellow => 93,
            Colors::LightBlue => 94,
            Colors::LightMagenta => 95,
            Colors::LightCyan => 96,
            Colors::White => 97,
            default => throw new RuntimeException(sprintf('Do not know how to handle color: %s', $termColor->name)),
        } + $offset;
    }

    private function modifierOnIndex(Attribute $modifier): int
    {
        return match($modifier) {
            Attribute::Reset => 0,
            Attribute::Bold => 1,
            Attribute::Dim => 2,
            Attribute::Italic => 3,
            Attribute::Underline => 4,
            Attribute::SlowBlink => 5,
            Attribute::RapidBlink => 6,
            Attribute::Hidden => 8,
            Attribute::Strike => 9,
            Attribute::Reverse => 7,
        };
    }

    private function modifierOffIndex(Attribute $modifier): int
    {
        return match($modifier) {
            Attribute::Reset => 0,
            Attribute::Bold => 22,
            Attribute::Dim => 22,
            Attribute::Italic => 23,
            Attribute::Underline => 24,
            Attribute::SlowBlink => 25,
            // same code as disabling slow blink according to crossterm
            Attribute::RapidBlink => 25,
            Attribute::Hidden => 28,
            Attribute::Strike => 29,
            Attribute::Reverse => 27,
        };
    }

    /**
     * Control sequence introducer
     */
    private function csi(string $action): string
    {
        return sprintf("\x1B[%s", $action);
    }

    /**
     * Operating system command
     */
    private function osc(string $action): string
    {
        return sprintf("\x1B]%s", $action);
    }
}
