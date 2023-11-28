<?php

declare(strict_types=1);

namespace PhpTui\Term;

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

final class Actions
{
    /**
     * Request the cursor position.
     *
     * This will (hopefully) be returned by the terminal and will be provided
     * as an `PhpTui\Term\Event\CursorPositionEvent`.
     */
    public static function requestCursorPosition(): RequestCursorPosition
    {
        return new RequestCursorPosition();
    }

    /**
     * Enable the alternate screen.
     *
     * Allows switching back to the users previous "screen" later.
     */
    public static function alternateScreenEnable(): AlternateScreenEnable
    {
        return new AlternateScreenEnable(true);
    }

    /**
     * Disable the alternate screen
     */
    public static function alternateScreenDisable(): AlternateScreenEnable
    {
        return new AlternateScreenEnable(false);
    }

    /**
     * Echo a standard string to the terminal
     */
    public static function printString(string $string): PrintString
    {
        return new PrintString($string);
    }

    /**
     * Show the cursor
     */
    public static function cursorShow(): CursorShow
    {
        return new CursorShow(true);
    }

    /**
     * Hide the cursor
     */
    public static function cursorHide(): CursorShow
    {
        return new CursorShow(false);
    }

    /**
     * Set the fore
     * ground color using RGB
     * @param int<0,255> $r
     * @param int<0,255> $g
     * @param int<0,255> $b
     */
    public static function setRgbForegroundColor(int $r, int $g, int $b): SetRgbForegroundColor
    {
        return new SetRgbForegroundColor($r, $g, $b);
    }

    /**
     * Set the background color using RGB
     * @param int<0,255> $r
     * @param int<0,255> $g
     * @param int<0,255> $b
     */
    public static function setRgbBackgroundColor(int $r, int $g, int $b): SetRgbBackgroundColor
    {
        return new SetRgbBackgroundColor($r, $g, $b);
    }

    /**
     * Set the foreground color to one of the ANSI base colors
     */
    public static function setForegroundColor(Colors $color): SetForegroundColor
    {
        return new SetForegroundColor($color);
    }

    /**
     * Set the background color to one of the ANSI base colors
     */
    public static function setBackgroundColor(Colors $color): SetBackgroundColor
    {
        return new SetBackgroundColor($color);
    }

    /**
     * Move the cursor to an absolute position.
     *
     * The top left cell is 0,0.
     *
     * @param int<0,max> $line
     * @param int<0,max> $col
     */
    public static function moveCursor(int $line, int $col): MoveCursor
    {
        return new MoveCursor($line, $col);
    }

    /**
     * Reset all modes (styles and colors)
     */
    public static function reset(): Reset
    {
        return new Reset();
    }

    /**
     * Enable or disable the bold styling
     */
    public static function bold(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Bold, $enable);
    }

    /**
     * Enable or disable the dim styling
     */
    public static function dim(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Dim, $enable);
    }

    /**
     * Enable or disable the italic styling
     */
    public static function italic(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Italic, $enable);
    }

    /**
     * Enable or disable the underline styling
     */
    public static function underline(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Underline, $enable);
    }

    /**
     * Enable or disable the slow blink styling
     */
    public static function slowBlink(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::SlowBlink, $enable);
    }

    /**
     * Enable or disable the rapid blink styling
     */
    public static function rapidBlink(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::RapidBlink, $enable);
    }

    /**
     * Enable or disable the reverse blink styling
     */
    public static function reverse(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Reverse, $enable);
    }

    /**
     * Enable or disable the hidden styling - useful for passwords.
     */
    public static function hidden(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Hidden, $enable);
    }

    /**
     * Enable or disable the strike-through styling
     */
    public static function strike(bool $enable): SetModifier
    {
        return new SetModifier(Attribute::Strike, $enable);
    }

    /**
     * Perform a clear operation.
     *
     * The type of clear operation is given with the Enum for example
     *
     * `Actions::clear(ClearType::All)`
     *
     * Will clear the entire screen.
     */
    public static function clear(ClearType $clearType): Clear
    {
        return new Clear($clearType);
    }

    /**
     * Enable mouse capture.
     *
     * Once this action has been issued mouse events will be made available.
     */
    public static function enableMouseCapture(): EnableMouseCapture
    {
        return new EnableMouseCapture(true);
    }

    /**
     * Disable mouse capture
     */
    public static function disableMouseCapture(): EnableMouseCapture
    {
        return new EnableMouseCapture(false);
    }

    /**
     * Scroll the terminal up the given number of rows
     * @param int<0,max> $rows
     */
    public static function scrollUp(int $rows = 1): ScrollUp
    {
        return new ScrollUp($rows);
    }

    /**
     * Scroll the terminal down the given number of rows
     * @param int<0,max> $rows
     */
    public static function scrollDown(int $rows = 1): ScrollDown
    {
        return new ScrollDown($rows);
    }

    /**
     * Set the title of the terminal for the current process.
     */
    public static function setTitle(string $title): SetTerminalTitle
    {
        return new SetTerminalTitle($title);
    }

    /**
     * Enable or disable line wrap
     */
    public static function lineWrap(bool $enable): EnableLineWrap
    {
        return new EnableLineWrap($enable);
    }

    /**
     * Move the cursor down and to the start of the next line (or the given number of lines)
     * @param int<0,max> $nbLines
     */
    public static function moveCursorNextLine(int $nbLines = 1): MoveCursorNextLine
    {
        return new MoveCursorNextLine($nbLines);
    }

    /**
     * Move the cursor up and to the start of the previous line (or the given number of lines)
     * @param int<0,max> $nbLines
     */
    public static function moveCursorPreviousLine(int $nbLines = 1): MoveCursorPrevLine
    {
        return new MoveCursorPrevLine($nbLines);
    }

    /**
     * Move the cursor to the given column (0 based)
     */
    public static function moveCursorToColumn(int $col): MoveCursorToColumn
    {
        return new MoveCursorToColumn($col);
    }

    /**
     * Move the cursor to the given row (0 based)
     * @param int<0,max> $rows
     */
    public static function moveCursorToRow(int $rows): MoveCursorToRow
    {
        return new MoveCursorToRow($rows);
    }

    /**
     * Move cursor up 1 or the given number of rows.
     * @param int<0,max> $rows
     */
    public static function moveCursorUp(int $rows = 1): MoveCursorUp
    {
        return new MoveCursorUp($rows);
    }


    /**
     * Move cursor right 1 or the given number of columns.
     * @param int<0,max> $cols
     */
    public static function moveCursorRight(int $cols): MoveCursorRight
    {
        return new MoveCursorRight($cols);
    }

    /**
     * Move cursor down 1 or the given number of rows.
     * @param int<0,max> $rows
     */
    public static function moveCursorDown(int $rows): MoveCursorDown
    {
        return new MoveCursorDown($rows);
    }

    /**
     * Move cursor left 1 or the given number of columns.
     * @param int<0,max> $cols
     */
    public static function moveCursorLeft(int $cols): MoveCursorLeft
    {
        return new MoveCursorLeft($cols);
    }

    /**
     * Save the cursor position
     */
    public static function saveCursorPosition(): SaveCursorPosition
    {
        return new SaveCursorPosition();
    }

    /**
     * Restore the cursor position
     */
    public static function restoreCursorPosition(): RestoreCursorPosition
    {
        return new RestoreCursorPosition();
    }

    /**
     * Enable cursor blinking
     */
    public static function enableCusorBlinking(): EnableCursorBlinking
    {
        return new EnableCursorBlinking(true);
    }

    /**
     * Disable cursor blinking
     */
    public static function disableCursorBlinking(): EnableCursorBlinking
    {
        return new EnableCursorBlinking(false);
    }

    /**
     * Set the cursor style
     */
    public static function setCursorStyle(CursorStyle $cursorStyle): SetCursorStyle
    {
        return new SetCursorStyle($cursorStyle);
    }
}
