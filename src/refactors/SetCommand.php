<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;

final readonly class SetCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private DateTime $datetime)
    {
    }

    public function execute(): array
    {
        $isLoaded = Memcached::set_debug_items_memcache($this->query_value_array['value']);
        return array_merge($this->query_value_array, ['set_status' => ['status' => $isLoaded, 'datetime' => $this->datetime,],]);
    }
}
