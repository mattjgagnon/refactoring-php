<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final class StatsCommand
{
    public function __construct(public array $query_value_array)
    {
    }

    public function execute()
    {
        $mc = Memcached::init();
        return array_merge($this->query_value_array, ['stats' => $mc->getStats()]);
    }
}
