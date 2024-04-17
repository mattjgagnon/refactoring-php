<?php

namespace refactors;

use mattjgagnon\RefactoringPhp\refactors\MemcachedAPI;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MemcachedAPITest extends TestCase
{
    #[Test] public function it_calls_memcached_api()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [];
        $session = [];

        // act
        $memcached = new MemcachedAPI($argv, $argc, $get, $session);
        $memcached->memcached_api();

        // assert
        $this->assertInstanceOf(MemcachedAPI::class, $memcached);
    }
}
