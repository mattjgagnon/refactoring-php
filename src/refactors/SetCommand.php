<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;

final readonly class SetCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private DateTime $datetime, private mixed $value)
    {
    }

    public function execute(): array
    {
        $isLoaded = Memcached::set_debug_items_memcache($this->value);
        return array_merge($this->query_value_array, ['set_status' => ['status' => $isLoaded, 'datetime' => $this->datetime,],]);
    }
}
