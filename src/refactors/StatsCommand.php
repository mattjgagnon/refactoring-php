<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;

final readonly class StatsCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private DateTime $datetime)
    {
    }

    public function execute(): array
    {
        $mc = Memcached::init();
        return array_merge($this->query_value_array, ['stats' => $mc->getStats(), 'datetime' => $this->datetime]);
    }
}
