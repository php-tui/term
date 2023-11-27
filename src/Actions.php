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
     * Set the foreground color using RGB
     */
    public static function setRgbForegroundColor(int $r, int $g, int $b): SetRgbForegroundColor
    {
        return new SetRgbForegroundColor($r, $g, $b);
    }

    /**
     * Set the background color using RGB
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
     * ```
     * Actions::clear(ClearType::All)
     * ```
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
     * Scroll the terminal up
     */
    public static function scrollUp(int $rows = 1): ScrollUp
    {
        return new ScrollUp($rows);
    }

    public static function scrollDown(int $rows = 1): ScrollDown
    {
        return new ScrollDown($rows);
    }

    public static function setTitle(string $title): SetTerminalTitle
    {
        return new SetTerminalTitle($title);
    }

    public static function lineWrap(bool $enable): EnableLineWrap
    {
        return new EnableLineWrap($enable);
    }

    public static function moveCursorNextLine(int $nbLines): MoveCursorNextLine
    {
        return new MoveCursorNextLine($nbLines);
    }

    public static function moveCursorPreviousLine(int $nbLines): MoveCursorPrevLine
    {
        return new MoveCursorPrevLine($nbLines);
    }

    public static function moveCursorToColumn(int $int): MoveCursorToColumn
    {
        return new MoveCursorToColumn($int);
    }

    public static function moveCursorToRow(int $int): MoveCursorToRow
    {
        return new MoveCursorToRow($int);
    }

    public static function moveCursorUp(int $int): MoveCursorUp
    {
        return new MoveCursorUp($int);
    }


    public static function moveCursorRight(int $int): MoveCursorRight
    {
        return new MoveCursorRight($int);
    }

    public static function moveCursorDown(int $int): MoveCursorDown
    {
        return new MoveCursorDown($int);
    }

    public static function moveCursorLeft(int $int): MoveCursorLeft
    {
        return new MoveCursorLeft($int);
    }

    public static function saveCursorPosition(): SaveCursorPosition
    {
        return new SaveCursorPosition();
    }

    public static function restoreCursorPosition(): RestoreCursorPosition
    {
        return new RestoreCursorPosition();
    }

    public static function enableCusorBlinking(): EnableCursorBlinking
    {
        return new EnableCursorBlinking(true);
    }

    public static function disableCursorBlinking(): EnableCursorBlinking
    {
        return new EnableCursorBlinking(false);
    }

    public static function setCursorStyle(CursorStyle $cursorStyle): SetCursorStyle
    {
        return new SetCursorStyle($cursorStyle);
    }
}
