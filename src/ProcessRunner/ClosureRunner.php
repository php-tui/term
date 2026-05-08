<?php

declare(strict_types=1);

namespace PhpTui\Term\ProcessRunner;

use BadMethodCallException;
use Closure;
use PhpTui\Term\ProcessResult;
use PhpTui\Term\ProcessRunner;

/**
 * Implementation to be used for test scenarios.
 */
final class ClosureRunner implements ProcessRunner
{
    /**
     * @param Closure(string[]): ProcessResult $closure
     */
    public function __construct(private readonly Closure $closure)
    {
    }

    /**
     * @throws BadMethodCallException
     */
    public function __sleep(): array
    {
        throw new BadMethodCallException('Cannot serialize '.__CLASS__);
    }

    /**
     * @throws BadMethodCallException
     */
    public function __wakeup(): void
    {
        throw new BadMethodCallException('Cannot unserialize '.__CLASS__);
    }

    public function run(array $command): ProcessResult
    {
        return ($this->closure)($command);
    }

    /**
     * @param Closure(string[]): ProcessResult $closure
     */
    public static function new(Closure $closure): self
    {
        return new self($closure);
    }
}
