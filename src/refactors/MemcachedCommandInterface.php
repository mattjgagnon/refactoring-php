<?php

namespace mattjgagnon\RefactoringPhp\refactors;

interface MemcachedCommandInterface
{
    public function execute(): array;
}
