<?php

namespace PhpTui\Term\Tests\EventProvider;

use PHPUnit\Framework\TestCase;
use PhpTui\Term\EventProvider\AggregateEventProvider;
use PhpTui\Term\EventProvider\ArrayEventProvider;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\KeyModifiers;

final class AggregateEventProviderTest extends TestCase
{
    public function testAggregateNullWithNoProviders(): void
    {
        $provider = new AggregateEventProvider([]);
        self::assertNull($provider->next());
    }

    public function testAggregateWithSecondProviderReturning(): void
    {
        $provider = new AggregateEventProvider([
            new ArrayEventProvider([
            ]),
            new ArrayEventProvider([
                CharKeyEvent::new('f', KeyModifiers::NONE),
            ]),
        ]);
        self::assertNotNull($provider->next());
        self::assertNull($provider->next());
    }

    public function testAggregateWithSequentialProviders(): void
    {
        $provider = new AggregateEventProvider([
            new ArrayEventProvider([
                CharKeyEvent::new('g', KeyModifiers::NONE),
            ]),
            new ArrayEventProvider([
                CharKeyEvent::new('f', KeyModifiers::NONE),
            ]),
        ]);
        self::assertNotNull($provider->next());
        self::assertNotNull($provider->next());
        self::assertNull($provider->next());
    }
}
