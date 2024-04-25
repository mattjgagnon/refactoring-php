<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class SetAllCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private string $datetime_formatted)
    {
    }

    public function execute(): array
    {
        $isLoaded = Memcached::set_all_debug_items_memcache();
        return array_merge($this->query_value_array, ['status' => $isLoaded, 'datetime' => $this->datetime_formatted]);
    }
}
