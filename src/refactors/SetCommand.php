<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class SetCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private string $datetime_formatted, private mixed $value)
    {
    }

    public function execute(): array
    {
        $isLoaded = Memcached::set_debug_items_memcache($this->value);
        return array_merge($this->query_value_array, ['set_status' => ['status' => $isLoaded, 'datetime' => $this->datetime_formatted,],]);
    }
}
