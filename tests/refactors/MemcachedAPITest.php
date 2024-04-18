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
        $argc = 2;
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

    #[Test] public function it_calls_memcached_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'stats' => '',
        ];
        $session = [];

        // act
        $memcached = new MemcachedAPI($argv, $argc, $get, $session);
        $results = $memcached->memcached_api();

        // assert
        $results_array = json_decode($results, 1);
        $this->assertArrayHasKey('command', $results_array);
        $this->assertIsArray($results_array);
        $this->assertIsArray($results_array['command']);
        $this->assertArrayHasKey('query', $results_array['command']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('stats', $results_array);
        $this->assertIsArray($results_array['stats']);
    }
}
