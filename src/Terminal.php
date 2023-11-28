<?php

declare(strict_types=1);

namespace PhpTui\Term;

use PhpTui\Term\EventProvider\AggregateEventProvider;
use PhpTui\Term\EventProvider\SignalEventProvider;
use PhpTui\Term\EventProvider\SyncTtyEventProvider;
use PhpTui\Term\InformationProvider\AggregateInformationProvider;
use PhpTui\Term\InformationProvider\SizeFromEnvVarProvider;
use PhpTui\Term\InformationProvider\SizeFromSttyProvider;
use PhpTui\Term\Painter\AnsiPainter;
use PhpTui\Term\RawMode\SttyRawMode;
use PhpTui\Term\Writer\StreamWriter;

final class Terminal
{
    /**
     * @var Action[]
     */
    private array $queue = [];

    public function __construct(
        private readonly Painter $painter,
        private readonly InformationProvider $infoProvider,
        private readonly RawMode $rawMode,
        private readonly EventProvider $eventProvider
    ) {
    }

    /**
     * Create a new terminal, if no backend is provided a standard ANSI
     * terminal will be created.
     */
    public static function new(
        Painter $painter = null,
        InformationProvider  $infoProvider = null,
        EventProvider $eventProvider = null,
        RawMode $rawMode = null,
    ): self {
        return new self(
            $painter ?? AnsiPainter::new(StreamWriter::stdout()),
            $infoProvider ?? AggregateInformationProvider::new([
                SizeFromEnvVarProvider::new(),
                SizeFromSttyProvider::new()
            ]),
            $rawMode ?? SttyRawMode::new(),
            $eventProvider ?? new AggregateEventProvider([
                SyncTtyEventProvider::new(),
                SignalEventProvider::registered(),
            ]),
        );
    }

    /**
     * Return information represented by the given class.
     *
     * @template T of TerminalInformation
     * @param class-string<T> $classFqn
     * @return T|null
     */
    public function info(string $classFqn): ?object
    {
        return $this->infoProvider->for($classFqn);
    }

    /**
     * Queue a painter action.
     */
    public function queue(Action ...$actions): self
    {
        foreach ($actions as $action) {
            $this->queue[] = $action;
        }

        return $this;
    }

    public function events(): EventProvider
    {
        return $this->eventProvider;
    }

    public function enableRawMode(): void
    {
        $this->rawMode->enable();
    }

    public function disableRawMode(): void
    {
        $this->rawMode->disable();
    }

    public function flush(): self
    {
        $this->painter->paint($this->queue);
        $this->queue = [];

        return $this;
    }

    public function execute(Action ...$actions): void
    {
        foreach ($actions as $action) {
            $this->painter->paint([$action]);
        }
    }
}
