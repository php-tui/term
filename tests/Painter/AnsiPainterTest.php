<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Actions;
use PhpTui\Term\AnsiParser;
use PhpTui\Term\ClearType;
use PhpTui\Term\Colors;
use PhpTui\Term\Painter\AnsiPainter;
use PhpTui\Term\Writer\BufferWriter;
use PHPUnit\Framework\TestCase;

final class AnsiPainterTest extends TestCase
{
    public function testControlSequences(): void
    {
        $this->assertCsiSeq('6n', Actions::requestCursorPosition());
        $this->assertCsiSeq('42m', Actions::setBackgroundColor(Colors::Green));
        $this->assertCsiSeq('34m', Actions::setForegroundColor(Colors::Blue));
        $this->assertCsiSeq('48;2;2;3;4m', Actions::setRgbBackgroundColor(2, 3, 4));
        $this->assertCsiSeq('38;2;2;3;4m', Actions::setRgbForegroundColor(2, 3, 4));
        $this->assertCsiSeq('?25l', Actions::cursorHide());
        $this->assertCsiSeq('?25h', Actions::cursorShow());
        $this->assertCsiSeq('?1049h', Actions::alternateScreenEnable());
        $this->assertCsiSeq('?1049l', Actions::alternateScreenDisable());
        $this->assertCsiSeq('2;3H', Actions::moveCursor(2, 3));
        $this->assertCsiSeq('0m', Actions::reset());
        $this->assertCsiSeq('1m', Actions::bold(true));
        $this->assertCsiSeq('2m', Actions::dim(true));
        $this->assertCsiSeq('3m', Actions::italic(true));
        $this->assertCsiSeq('23m', Actions::italic(false));
        $this->assertCsiSeq('4m', Actions::underline(true));
        $this->assertCsiSeq('24m', Actions::underline(false));
        $this->assertCsiSeq('5m', Actions::slowBlink(true));
        $this->assertCsiSeq('7m', Actions::reverse(true));
        $this->assertCsiSeq('8m', Actions::hidden(true));
        $this->assertCsiSeq('9m', Actions::strike(true));
        $this->assertCsiSeq('2J', Actions::clear(ClearType::All));
        $this->assertCsiSeq('3J', Actions::clear(ClearType::Purge));
        $this->assertCsiSeq('J', Actions::clear(ClearType::FromCursorDown));
        $this->assertCsiSeq('1J', Actions::clear(ClearType::FromCursorUp));
        $this->assertCsiSeq('2K', Actions::clear(ClearType::CurrentLine));
        $this->assertCsiSeq('K', Actions::clear(ClearType::UntilNewLine));
        $this->assertCsiSeq('S', Actions::scrollUp());
        $this->assertCsiSeq('T', Actions::scrollDown());
        $this->assertCsiSeq('?7h', Actions::lineWrap(true));
        $this->assertCsiSeq('?7l', Actions::lineWrap(false));
        $this->assertCsiSeq('1E', Actions::moveCursorNextLine(1));
        $this->assertCsiSeq('1F', Actions::moveCursorPreviousLine(1));
        $this->assertCsiSeq('1G', Actions::moveCursorToColumn(0));
        $this->assertCsiSeq('1d', Actions::moveCursorToRow(0));
        $this->assertCsiSeq('1A', Actions::moveCursorUp(1));
        $this->assertCsiSeq('1C', Actions::moveCursorRight(1));
        $this->assertCsiSeq('1B', Actions::moveCursorDown(1));
        $this->assertCsiSeq('1D', Actions::moveCursorLeft(1));
        $this->assertRawSeq("\xB7", Actions::saveCursorPosition());
        $this->assertRawSeq("\xB8", Actions::restoreCursorPosition());
        $this->assertCsiSeq("?12h", Actions::enableCusorBlinking());
        $this->assertCsiSeq("?12l", Actions::disableCursorBlinking());
        $this->assertRawSeq("\x1b[0 q", Actions::setCursorStyle(CursorStyle::DefaultUserShape);
        $this->assertRawSeq("\x1b[1 q", Actions::setCursorStyle(CursorStyle::BlinkingBlock);
        $this->assertRawSeq("\x1b[2 q", Actions::setCursorStyle(CursorStyle::SteadyBlock);
        $this->assertRawSeq("\x1b[3 q", Actions::setCursorStyle(CursorStyle::BlinkingUnderscore);
        $this->assertRawSeq("\x1b[4 q", Actions::setCursorStyle(CursorStyle::SteadyUnderscore);
        $this->assertRawSeq("\x1b[5 q", Actions::setCursorStyle(CursorStyle::Blinkingbar);
        $this->assertRawSeq("\x1b[6 q", Actions::setCursorStyle(CursorStyle::SteadyBar);

        $this->assertCsiSeq("0;Hello\x07", Actions::setTitle('Hello'));

        $this->assertCsiSeq("\x1B[?1000h\x1B[?1002h\x1B[?1003h\x1B[?1015h\x1B[?1006h", Actions::enableMouseCapture());
        $this->assertCsiSeq("\x1B[?1006h\x1B[?1015h\x1B[?1003h\x1B[?1002h\x1B[?1000h", Actions::disableMouseCapture());
    }
    private function assertCsiSeq(string $string, Action $command): void
    {
        $this->assertSeq("\033[", $string, $command);
    }

    private function assertOscSeq(string $string, Action $command): void
    {
        $this->assertSeq("\033]", $string, $command);
    }

    private function assertSeq(string $prefix, string $string, Action $command): void
    {
        $writer = BufferWriter::new();
        $term = AnsiPainter::new($writer);
        $term->paint([$command]);
        self::assertEquals(json_encode(sprintf('%s%s', $prefix, $string)), json_encode($writer->toString()), $command::class);
        $parsedCommand = AnsiParser::parseString($writer->toString(), true);
        self::assertEquals($command, $parsedCommand[0], 'parsing output');
    }

    private function assertRawSeq(string $string, Action $command): void
    {
        $writer = BufferWriter::new();
        $term = AnsiPainter::new($writer);
        $term->paint([$command]);
        self::assertEquals(json_encode($string), json_encode($writer->toString()), $command::class);
    }
}
