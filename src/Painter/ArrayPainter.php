<?php

declare(strict_types=1);

namespace PhpTui\Term\Painter;

use PhpTui\Term\Action;
use PhpTui\Term\Painter;

/**
 * Painter for testing which stores all the actions passed to it and
 * exposes them for inspection.
 */
final class ArrayPainter implements Painter
{
    /**
     * @param Action[] $actions
     */
    private function __construct(private array $actions = [])
    {
    }

    public static function new(): self
    {
        return new self([]);
    }

    public function paint(array $actions): void
    {
        $this->actions = array_merge($this->actions, $actions);
    }
    /**
     * @return Action[]
     */
    public function actions(): array
    {
        return $this->actions;
    }
}
