<?php

namespace refactors;

use mattjgagnon\RefactoringPhp\refactors\MemcachedAPI;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MemcachedAPITest extends TestCase
{
    #[Test] public function it_calls_memcached_api_and_responds_with_default_message()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [];
        $session = [];

        // act
        $memcached = new MemcachedAPI($argv, $argc, $get, $session);
        $results = $memcached->memcached_api();

        // assert
        $this->assertIsString($results);
        $this->assertSame('{
    "message": "tbd -list all commands here"
}', $results);
    }
}
