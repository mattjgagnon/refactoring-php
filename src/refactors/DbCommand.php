<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class DbCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private string $value)
    {
    }

    public function execute(): array
    {
        $db = Memcached::get_tbl_debug_items($this->value);
        return array_merge($this->query_value_array, ['tbl_debug_items' => $db]);
    }
}
