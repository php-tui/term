<?php

declare(strict_types=1);

namespace PhpTui\Term\Tests\InformationProvider;

use PhpTui\Term\InformationProvider\SizeFromEnvVarProvider;
use PhpTui\Term\TerminalInformation\Size;
use PHPUnit\Framework\TestCase;

final class SizeFromEnvVarProviderTest extends TestCase
{
    public function testProvider(): void
    {
        [$lines, $cols] = [getenv('LINES'), getenv('COLUMNS')];
        putenv('LINES=');
        putenv('COLUMNS=');

        $size = (new SizeFromEnvVarProvider())->for(Size::class);
        self::assertNull($size);
        putenv('LINES=1');
        putenv('COLUMNS=2');

        $size = (new SizeFromEnvVarProvider())->for(Size::class);
        self::assertEquals(new Size(1, 2), $size);

    }
}
