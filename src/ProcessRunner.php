<?php

declare(strict_types=1);

namespace PhpTui\Term;

interface ProcessRunner
{
    public function __sleep():array;
    public function __wakeup(): void;

    /**
     * @param string[] $command
     */
    public function run(array $command): ProcessResult;
}
