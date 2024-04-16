<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;
use DateTimeZone;

final readonly class MemcachedAPI
{
    public function __construct(
        private readonly array $argv,
        private readonly int $argc,
        private readonly array $get,
        private readonly array $session,
    ) {
    }

    public function memcached_api(): void
    {
        $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
        $mc = Memcached::init();

        $query = null;
        $value = null;

        $isCLI = (php_sapi_name() == 'cli');

        if ($isCLI && $this->argc > 1) {
            $input_array = explode('=', $this->argv[1]);
            if (count($input_array) > 0) $query = $input_array[0];
            if (count($input_array) > 1) $value = $input_array[1];
        } else {
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
        }

        $query_value_array = array('command' => array('query' => $query, 'value' => $value, 'hostname' => gethostname()));

        switch ($query) {
            case 'stats':

                echo json_encode(array_merge($query_value_array, array('stats' => $mc->getStats())), JSON_PRETTY_PRINT);
                break;

            case 'set_all':
                $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
                $datetime = $datetime->format('F j, Y H:i:s');
                $isLoaded = Memcached::set_all_debug_items_memcache();
                $result = array_merge($query_value_array, array("status" => $isLoaded, "datetime" => $datetime));
                echo json_encode($result, JSON_PRETTY_PRINT);
                break;

            case 'set':
                $datetime = new DateTime('now', new DateTimeZone('America/New_York'));
                $datetime = $datetime->format('F j, Y H:i:s');
                $isLoaded = Memcached::set_debug_items_memcache($value);
                $result = array_merge($query_value_array, array('set_status' => array("status" => $isLoaded, "datetime" => $datetime)));
                echo json_encode($result, JSON_PRETTY_PRINT);
                break;

            case 'get':
                $cached_data = Memcached::get_debug_items_memcache($value);
                echo json_encode(array_merge($query_value_array, array('memcached_data' => $cached_data)), JSON_PRETTY_PRINT);
                break;

            case 'get_all':
                $cached_data = Memcached::get_all_debug_items_memcache();
                echo json_encode(array_merge($query_value_array, array('memcached_data' => $cached_data)), JSON_PRETTY_PRINT);
                break;

            case 'get_keys':
                $keys = Memcached::get_all_debug_item_keys();
                echo json_encode(array_merge($query_value_array, array('db_keys' => $keys)), JSON_PRETTY_PRINT);
                break;

            case 'db':
                $db = Memcached::get_tbl_debug_items($this->get['db']);
                echo json_encode(array_merge($query_value_array, array('tbl_debug_items' => $db)), JSON_PRETTY_PRINT);
                break;

            case 'flush':
                $isFlushed = Memcached::flush_debug_items_for_memcache();
                $result = array_merge($query_value_array, array("status" => $isFlushed, "datetime" => $datetime));
                echo json_encode($result, JSON_PRETTY_PRINT);
                break;

            case 'benchmark':
                $this->run_memcached_benchmark($value);
                break;

            default:
                $result = array("message" => "tbd -list all commands here");
                echo json_encode($result, JSON_PRETTY_PRINT);
        }
    }

    private function run_memcached_benchmark($value = 1)
    {
        $memcached_data = new MemcachedData();
        $iteration = $value ?? 1;
        $result = ['iteration' => $iteration];
        $time_start_static = microtime(true);

        for ($i = 0; $i < $iteration; $i++) {
            if ($memcached_data->data != null) {
                $cached_data = $memcached_data->data;
            } else {
                //$time_start_memcached = microtime(true);
                $memcached_data->data = Memcached::get_all_debug_items_memcache();
                //$time_end_memcached = microtime(true);
                //$result['memcached'] = round(($time_end_memcached - $time_start_memcached) * 1000);
            }
        }

        $time_end_static = microtime(true);

        $result['static'] = round(($time_end_static - $time_start_static) * 1000);

        //    session_start();
        //    session_destroy();
        $start_session_time = microtime(true);
        //session_start();

        for ($i = 0; $i < $iteration; $i++) {
            if (isset($this->session['lg_debug_items'])) {
                $cached_data = $this->session['lg_debug_items'];
            } else {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $_SESSION['lg_debug_items'] = $cached_data;
            }
        }

        $time_session_end = microtime(true);

        $result['session'] = round(($time_session_end - $start_session_time) * 1000);

        $time_globals_start = microtime(true);

        for ($i = 0; $i < $iteration; $i++) {
            if (isset($GLOBALS["lg_debug_items"])) {
                $cached_data = $GLOBALS["lg_debug_items"];
            } else {
                $cached_data = Memcached::get_all_debug_items_memcache();
                $GLOBALS["lg_debug_items"] = $cached_data;
            }
        }

        $time_globals_end = microtime(true);

        $result['globals'] = round(($time_globals_end - $time_globals_start) * 1000);

        echo json_encode($result);
    }
}