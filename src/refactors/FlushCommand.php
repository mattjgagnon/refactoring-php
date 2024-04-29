<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class FlushCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array)
    {
    }

    public function execute(): array
    {
        $isFlushed = Memcached::flush_debug_items_for_memcache();
        return array_merge($this->query_value_array, ['status' => $isFlushed]);
    }
}
