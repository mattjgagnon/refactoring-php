<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class ListCommand implements MemcachedCommandInterface
{
    private const MSG_LIST_COMMANDS = 'tbd -list all commands here';

    public function __construct(private array $query_value_array)
    {
    }

    public function execute(): array
    {
        return array_merge($this->query_value_array, ['message' => self::MSG_LIST_COMMANDS]);
    }
}
