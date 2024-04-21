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
        $argv = [
            0 => '',
            1 => 'value1=value2',
        ];
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

    #[Test] public function it_calls_memcached_with_set_all_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'set_all' => '',
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
        $this->assertSame('set_all', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('datetime', $results_array);
    }

    #[Test] public function it_calls_memcached_with_set_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'set' => '',
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
        $this->assertSame('set', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertIsArray($results_array['set_status']);
        $this->assertArrayHasKey('status', $results_array['set_status']);
        $this->assertArrayHasKey('datetime', $results_array['set_status']);
    }

    #[Test] public function it_calls_memcached_with_get_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'get' => '',
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
        $this->assertSame('get', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('memcached_data', $results_array);
        $this->assertIsArray($results_array['memcached_data']);
    }

    #[Test] public function it_calls_memcached_with_get_all_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'get_all' => '',
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
        $this->assertSame('get_all', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('memcached_data', $results_array);
        $this->assertIsArray($results_array['memcached_data']);
    }

    #[Test] public function it_calls_memcached_with_get_keys_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'get_keys' => '',
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
        $this->assertSame('get_keys', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('db_keys', $results_array);
        $this->assertIsArray($results_array['db_keys']);
    }

    #[Test] public function it_calls_memcached_with_db_api_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'db' => '',
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
        $this->assertSame('db', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('tbl_debug_items', $results_array);
        $this->assertIsArray($results_array['tbl_debug_items']);
    }

    #[Test] public function it_calls_memcached_with_flush_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'flush' => '',
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
        $this->assertSame('flush', $results_array['command']['query']);
        $this->assertArrayHasKey('value', $results_array['command']);
        $this->assertArrayHasKey('hostname', $results_array['command']);
        $this->assertArrayHasKey('status', $results_array);
        $this->assertIsArray($results_array['status']);
        $this->assertArrayHasKey('datetime', $results_array);
        $this->assertIsArray($results_array['datetime']);
        $this->assertArrayHasKey('date', $results_array['datetime']);
        $this->assertArrayHasKey('timezone_type', $results_array['datetime']);
        $this->assertArrayHasKey('timezone', $results_array['datetime']);
    }

    #[Test] public function it_calls_memcached_with_benchmark_and_responds()
    {
        // assemble
        $argv = [];
        $argc = 0;
        $get = [
            'benchmark' => '',
        ];
        $session = [];

        // act
        $memcached = new MemcachedAPI($argv, $argc, $get, $session);
        $results = $memcached->memcached_api();

        // assert
        $results_array = json_decode($results, 1);
        $this->assertIsArray($results_array);
        $this->assertArrayHasKey('iteration', $results_array);
        $this->assertArrayHasKey('static', $results_array);
        $this->assertArrayHasKey('session', $results_array);
        $this->assertArrayHasKey('globals', $results_array);
    }
}
