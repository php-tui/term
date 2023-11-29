<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\InformationProvider;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PhpTui\Term\InformationProvider\SizeFromSttyProvider;
use PhpTui\Term\ProcessResult;
use PhpTui\Term\ProcessRunner\ClosureRunner;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class SizeFromSttyProviderTest extends TestCase
{
    #[DataProvider('provideSizeFromStty')]
    public function testSizeFromStty(string $output, Size $expected): void
    {
        $runner = ClosureRunner::new(function (array $command) use ($output): ProcessResult {
            return new ProcessResult(
                0,
                $output,
                '',
            );
        });

        $provider = SizeFromSttyProvider::new($runner);
        self::assertEquals($expected, $provider->for(Size::class));
    }

    public static function provideSizeFromStty(): Generator
    {
        yield 'normal' => [
                <<<'EOT'
                    speed 38400 baud; rows 42; columns 140; line = 0;
                    intr = ^C; quit = ^\; erase = ^?; kill = ^U; eof = ^D; eol = <undef>; eol2 = <undef>; swtch = <undef>; start = ^Q; stop = ^S; susp = ^Z;
                    rprnt = ^R; werase = ^W; lnext = ^V; discard = ^O; min = 1; time = 0;
                    -parenb -parodd -cmspar cs8 -hupcl -cstopb cread -clocal -crtscts
                    -ignbrk -brkint -ignpar -parmrk -inpck -istrip -inlcr -igncr icrnl -ixon -ixoff -iuclc -ixany -imaxbel iutf8
                    opost -olcuc -ocrnl onlcr -onocr -onlret -ofill -ofdel nl0 cr0 tab0 bs0 vt0 ff0
                    isig icanon iexten echo echoe echok -echonl -noflsh -xcase -tostop -echoprt echoctl echoke -flusho -extproc
                    EOT,
            new Size(42, 140)

        ];

        yield 'alternate' => [
                <<<'EOT'
                     speed 9600 baud; 23 rows; 309 columns;
                    lflags: icanon isig iexten echo echoe -echok echoke -echonl echoctl
                            -echoprt -altwerase -noflsh -tostop -flusho pendin -nokerninfo
                            -extproc
                    iflags: -istrip icrnl -inlcr -igncr ixon -ixoff ixany imaxbel -iutf8
                            -ignbrk brkint -inpck -ignpar -parmrk
                    oflags: opost onlcr -oxtabs -onocr -onlret
                    cflags: cread cs8 -parenb -parodd hupcl -clocal -cstopb -crtscts -dsrflow
                            -dtrflow -mdmbuf
                    cchars: discard = ^O; dsusp = ^Y; eof = ^D; eol = <undef>;
                            eol2 = <undef>; erase = ^?; intr = ^C; kill = ^U; lnext = ^V;
                            min = 1; quit = ^\; reprint = ^R; start = ^Q; status = ^T;
                            stop = ^S; susp = ^Z; time = 0; werase = ^W;                   
                    EOT,
            new Size(23, 309)

        ];
    }

    public function testSizeFromSttyNoMatch(): void
    {
        $runner = ClosureRunner::new(function (array $command): ProcessResult {
            return new ProcessResult(
                0,
                <<<'EOT'
                    foobar
                    EOT,
                ''
            );
        });

        $provider = SizeFromSttyProvider::new($runner);
        $size = $provider->for(Size::class);
        self::assertNull($size);
    }
}
