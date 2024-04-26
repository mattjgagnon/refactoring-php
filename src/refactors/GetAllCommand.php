<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class GetAllCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array)
    {
    }

    public function execute(): array
    {
        $cached_data = Memcached::get_all_debug_items_memcache();
        return array_merge($this->query_value_array, ['memcached_data' => $cached_data]);
    }
}
