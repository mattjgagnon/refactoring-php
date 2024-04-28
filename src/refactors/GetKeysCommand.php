<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;

final readonly class GetKeysCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private DateTime $datetime)
    {
    }

    public function execute(): array
    {
        $keys = Memcached::get_all_debug_item_keys();
        return array_merge($this->query_value_array, ['db_keys' => $keys, 'datetime' => $this->datetime]);
    }
}
