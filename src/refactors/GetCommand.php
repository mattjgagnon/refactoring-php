<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;

final readonly class GetCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private DateTime $datetime, private string $value)
    {
    }

    public function execute(): array
    {
        $cached_data = Memcached::get_debug_items_memcache($this->value);
        return array_merge($this->query_value_array, ['memcached_data' => $cached_data, 'datetime' => $this->datetime,]);
    }
}
