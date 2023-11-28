<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests;

use PhpTui\Term\Action;
use PhpTui\Term\Actions;
use PhpTui\Term\Colors;
use PhpTui\Term\Painter\TestPainter;
use PhpTui\Term\RawMode\TestRawMode;
use PhpTui\Term\Terminal;
use PHPUnit\Framework\TestCase;

final class TerminalTest extends TestCase
{

    public function testEnableRawMode(): void
    {
        $dummy = TestPainter::new();
        $rawMode = new TestRawMode();
        $term = Terminal::new(rawMode: $rawMode);
        self::assertFalse($rawMode->isEnabled());
        $term->enableRawMode();
        self::assertTrue($rawMode->isEnabled());
        $term->disableRawMode();
        self::assertFalse($rawMode->isEnabled());
    }

    public function testExecute(): void
    {
        $dummy = TestPainter::new();
        $term = Terminal::new($dummy);
        $term->execute(
            Actions::enableMouseCapture(),
            Actions::moveCursorNextLine(),
        );
        self::assertCount(2, $dummy->actions());
        self::assertEquals(
            [
                'EnableMouseCapture(true)',
                'MoveCursorNextLine(1)',
            ],
            array_map(fn (Action $a) => $a->__toString(), $dummy->actions()),
        );
    }

    public function testQueueActions(): void
    {
        $dummy = TestPainter::new();
        $term = Terminal::new($dummy)

            ->queue(
                Actions::alternateScreenDisable(),
                Actions::alternateScreenEnable(),
                Actions::printString('Hello World'),
                Actions::cursorShow(),
                Actions::cursorHide(),
                Actions::setRgbForegroundColor(0, 127, 255),
                Actions::setRgbBackgroundColor(255, 0, 127),
                Actions::setForegroundColor(Colors::Red),
                Actions::setBackgroundColor(Colors::Blue),
                Actions::moveCursor(1, 2),
                Actions::reset(),
                Actions::bold(true),
                Actions::dim(true),
                Actions::italic(true),
                Actions::underline(true),
                Actions::slowBlink(true),
                Actions::rapidBlink(true),
                Actions::reverse(true),
                Actions::hidden(true),
                Actions::strike(true)
            )
            ->flush();

        self::assertCount(20, $dummy->actions());
        self::assertEquals(
            [
                'AlternateScreenEnable(false)',
                'AlternateScreenEnable(true)',
                'Print("Hello World")',
                'CursorShow(true)',
                'CursorShow(false)',
                'SetRgbForegroundColor(0, 127, 255)',
                'SetRgbBackgroundColor(255, 0, 127)',
                'SetForegroundColor(Red)',
                'SetBackgroundColor(Blue)',
                'MoveCursor(line=1,col=2)',
                'Reset()',
                'SetModifier(Bold,on)',
                'SetModifier(Dim,on)',
                'SetModifier(Italic,on)',
                'SetModifier(Underline,on)',
                'SetModifier(SlowBlink,on)',
                'SetModifier(RapidBlink,on)',
                'SetModifier(Reverse,on)',
                'SetModifier(Hidden,on)',
                'SetModifier(Strike,on)',
            ],
            array_map(static fn (Action $cmd): string => $cmd->__toString(), $dummy->actions())
        );
    }
}
