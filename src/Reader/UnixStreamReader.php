<?php

declare(strict_types=1);

namespace PhpTui\Term\Reader;

use PhpTui\Term\Reader;

final class UnixStreamReader implements Reader
{
    /**
     * @param resource $stream
     */
    private function __construct(private $stream)
    {
    }

    public static function new(): self
    {
        // TODO: open `/dev/tty` is STDIN is not a TTY
        $resource = STDIN;
        stream_set_blocking($resource, false);

        return new self($resource);
    }

    public function read(): ?string
    {
        $bytes = stream_get_contents($this->stream);
        if ('' === $bytes || false === $bytes) {
            return null;
        }

        return $bytes;
    }
}
