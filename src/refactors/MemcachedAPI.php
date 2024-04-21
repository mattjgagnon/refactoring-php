<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;
use DateTimeZone;
use Exception;

final readonly class MemcachedAPI
{
    public function __construct(private array $argv, private int $argc, private array $get, private array $session,)
    {
    }

    /**
     * @throws Exception
     */
    public function memcached_api(): false|string
    {
        $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
        $mc = Memcached::init();

        [$query, $value] = $this->get_query_value();

        $query_value_array = ['command' => ['query' => $query, 'value' => $value, 'hostname' => gethostname()]];

        switch ($query) {
            case 'stats':
                return json_encode(array_merge($query_value_array, ['stats' => $mc->getStats()]), JSON_PRETTY_PRINT);

            case 'set_all':
                $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
                $datetime = $datetime->format('F j, Y H:i:s');
                $isLoaded = Memcached::set_all_debug_items_memcache();
                $result = array_merge($query_value_array, ["status" => $isLoaded, "datetime" => $datetime]);
                return json_encode($result, JSON_PRETTY_PRINT);

            case 'set':
                $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
                $datetime = $datetime->format('F j, Y H:i:s');
                $isLoaded = Memcached::set_debug_items_memcache($value);
                $result = array_merge($query_value_array, ['set_status' => ["status" => $isLoaded, "datetime" => $datetime]]);
                return json_encode($result, JSON_PRETTY_PRINT);

            case 'get':
                $cached_data = Memcached::get_debug_items_memcache($value);
                return json_encode(array_merge($query_value_array, ['memcached_data' => $cached_data]), JSON_PRETTY_PRINT);

            case 'get_all':
                $cached_data = Memcached::get_all_debug_items_memcache();
                return json_encode(array_merge($query_value_array, ['memcached_data' => $cached_data]), JSON_PRETTY_PRINT);

            case 'get_keys':
                $keys = Memcached::get_all_debug_item_keys();
                return json_encode(array_merge($query_value_array, ['db_keys' => $keys]), JSON_PRETTY_PRINT);

            case 'db':
                $db = Memcached::get_tbl_debug_items($this->get['db']);
                return json_encode(array_merge($query_value_array, ['tbl_debug_items' => $db]), JSON_PRETTY_PRINT);

            case 'flush':
                $isFlushed = Memcached::flush_debug_items_for_memcache();
                $result = array_merge($query_value_array, ["status" => $isFlushed, "datetime" => $datetime]);
                return json_encode($result, JSON_PRETTY_PRINT);

            case 'benchmark':
                return $this->run_memcached_benchmark($value);

            default:
                $result = ["message" => "tbd -list all commands here"];
                return json_encode($result, JSON_PRETTY_PRINT);
        }
    }

    private function get_query_value(): array
    {
        $query = NULL;
        $value = NULL;

        $isCLI = (php_sapi_name() == 'cli');

        if ($isCLI && $this->argc > 1) {
            return $this->get_cli_values($query, $value);
        } else {
            return $this->get_params($query, $value);
        }
    }

    private function get_cli_values(?string $query, ?string $value): array
    {
        $input_array = explode('=', $this->argv[1]);

        if (count($input_array) > 0) {
            $query = $input_array[0];
        }

        if (count($input_array) > 1) {
            $value = $input_array[1];
        }

        return [$query, $value];
    }

    private function get_params(?string $query, mixed $value): array
    {
        if (isset($this->get['stats'])) {
            // return memcached stats
            $query = 'stats';
            $value = $this->get['stats'];
        } elseif (isset($this->get['set_all'])) {
            // sets all debug item keys
            $query = 'set_all';

        } elseif (isset($this->get['set'])) {
            $query = 'set';
            $value = $this->get['set'];

        } elseif (isset($this->get['get'])) {
            $query = 'get';
            $value = $this->get['get'];

        } elseif (isset($this->get['get_all'])) {
            $query = 'get_all';

        } elseif (isset($this->get['get_keys'])) {
            $query = 'get_keys';

        } elseif (isset($this->get['db'])) {
            $query = 'db';

        } elseif (isset($this->get['flush'])) {
            $query = 'flush';

        } elseif (isset($this->get['benchmark'])) {
            $query = 'benchmark';
        }

        return [$query, $value];
    }

    private function run_memcached_benchmark($value = 1): false|string
    {
        $memcached_data = new MemcachedData();
        $iteration = $value ?? 1;
        $result = ['iteration' => $iteration];
        $time_start_static = microtime(TRUE);

        for ($i = 0; $i < $iteration; $i++) {
            if ($memcached_data->data == NULL) {
                $memcached_data->data = Memcached::get_all_debug_items_memcache();
            }
        }

        $time_end_static = microtime(TRUE);

        $result['static'] = round(($time_end_static - $time_start_static) * 1000);

        $start_session_time = microtime(TRUE);

        for ($i = 0; $i < $iteration; $i++) {
            if (!isset($this->session['lg_debug_items'])) {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $_SESSION['lg_debug_items'] = $cached_data;
            }
        }

        $time_session_end = microtime(TRUE);

        $result['session'] = round(($time_session_end - $start_session_time) * 1000);

        $time_globals_start = microtime(TRUE);

        for ($i = 0; $i < $iteration; $i++) {
            if (!isset($GLOBALS["lg_debug_items"])) {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $GLOBALS["lg_debug_items"] = $cached_data;
            }
        }

        $time_globals_end = microtime(TRUE);

        $result['globals'] = round(($time_globals_end - $time_globals_start) * 1000);

        return json_encode($result);
    }
}
