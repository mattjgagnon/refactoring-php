<?php

namespace mattjgagnon\RefactoringPhp\refactors;

final readonly class BenchmarkCommand implements MemcachedCommandInterface
{
    public function __construct(private array $query_value_array, private mixed $value)
    {
    }

    public function execute(): array
    {
        $result = $this->run_memcached_benchmark($this->value);
        return array_merge($this->query_value_array, $result);
    }

    private function run_memcached_benchmark($value = 1): array
    {
        $memcached_data = new MemcachedData();
        $iteration = $value ?? 1;
        $result = ['iteration' => $iteration];
        $time_start_static = microtime(TRUE);

        $this->populate_memcached_data($iteration, $memcached_data);

        $time_end_static = microtime(TRUE);

        $result['static'] = round(($time_end_static - $time_start_static) * 1000);

        $start_session_time = microtime(TRUE);

        $this->populate_session($iteration);

        $time_session_end = microtime(TRUE);

        $result['session'] = round(($time_session_end - $start_session_time) * 1000);

        $time_globals_start = microtime(TRUE);

        $this->populate_globals($iteration);

        $time_globals_end = microtime(TRUE);

        $result['globals'] = round(($time_globals_end - $time_globals_start) * 1000);

        return $result;
    }

    private function populate_memcached_data(mixed $iteration, MemcachedData $memcached_data): void
    {
        for ($i = 0; $i < $iteration; $i++) {
            if ($memcached_data->data == NULL) {
                $memcached_data->data = Memcached::get_all_debug_items_memcache();
            }
        }
    }

    private function populate_session(mixed $iteration): void
    {
        for ($i = 0; $i < $iteration; $i++) {
            if (!isset($this->session['lg_debug_items'])) {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $_SESSION['lg_debug_items'] = $cached_data;
            }
        }
    }

    private function populate_globals(mixed $iteration): void
    {
        for ($i = 0; $i < $iteration; $i++) {
            if (!isset($GLOBALS['lg_debug_items'])) {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $GLOBALS['lg_debug_items'] = $cached_data;
            }
        }
    }
}
