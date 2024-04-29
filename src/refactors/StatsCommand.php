<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class StatsCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array)
    {
    }

    public function execute(): array
    {
        $mc = Memcached::init();
        return array_merge($this->query_value_array, ['stats' => $mc->getStats()]);
    }
}
