<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final class ListCommand implements MemcachedCommandInterface
{
    private const MSG_LIST_COMMANDS = 'tbd -list all commands here';

    public function __construct()
    {
    }

    public function execute(): array
    {
        return ['message' => self::MSG_LIST_COMMANDS];
    }
}
