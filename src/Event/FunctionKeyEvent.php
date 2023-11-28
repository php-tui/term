<?php

declare(strict_types=1);

namespace PhpTui\Term\Event;

use PhpTui\Term\KeyEventKind;
use PhpTui\Term\KeyModifiers;

final class FunctionKeyEvent implements KeyEvent
{
    /**
     * @param int-mask-of<KeyModifiers::*> $modifiers
     */
    private function __construct(
        /**
         * @var int<1,17>
         */
        public readonly int $number,
        /**
         * @var int-mask-of<KeyModifiers::*>
         */
        public readonly int $modifiers,
        public readonly KeyEventKind $kind
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            'FunctionKey(number: %s, modifier: %s, kind: %s)',
            $this->number,
            KeyModifiers::toString($this->modifiers),
            $this->kind->name
        );
    }

    /**
     * @param int<1,17> $number
     * @param int-mask-of<KeyModifiers::*> $modifiers
     */
    public static function new(int $number, int $modifiers = KeyModifiers::NONE, KeyEventKind $kind = KeyEventKind::Press): self
    {
        return new self($number, $modifiers, $kind);
    }
}
