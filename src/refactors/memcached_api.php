<?php

header('Content-Type: application/json');

// assume that this includes a number of php bootstrap files
require_once __DIR__."/../site_init.php";

// assume this will have to be stubbed
use SomeCompany\Memcached;

$arguments = $argv;
$arguments_count = $argc;
memcached_api($arguments, $arguments_count);

function memcached_api($argv, $argc): void
{
    $mc = Memcached::init();

    $query = null;
    $value = null;

    $isCLI = (php_sapi_name() == 'cli');

    if ($isCLI && $argc > 1) {
        $input_array = explode('=', $argv[1]);
        if (count($input_array) > 0) $query = $input_array[0];
        if (count($input_array) > 1) $value = $input_array[1];
    } else {
        if (isset($_GET['stats'])) {
            // return memcached stats
            $query = 'stats';
            $value = $_GET['stats'];
        } elseif (isset($_GET['set_all'])) {
            // sets all debug item keys
            $query = 'set_all';

        } elseif (isset($_GET['set'])) {
            $query = 'set';
            $value = $_GET['set'];

        } elseif (isset($_GET['get'])) {
            $query = 'get';
            $value = $_GET['get'];

        } elseif (isset($_GET['get_all'])) {
            $query = 'get_all';

        } elseif (isset($_GET['get_keys'])) {
            $query = 'get_keys';

        } elseif (isset($_GET['db'])) {
            $query = 'db';

        } elseif (isset($_GET['flush'])) {
            $query = 'flush';

        } elseif (isset($_GET['benchmark'])) {
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
            $db = Memcached::get_tbl_debug_items($_GET['db']);
            echo json_encode(array_merge($query_value_array, array('tbl_debug_items' => $db)), JSON_PRETTY_PRINT);
            break;

        case 'flush':
            $isFlushed = Memcached::flush_debug_items_for_memcache();
            $result = array_merge($query_value_array, array("status" => $isFlushed, "datetime" => $datetime));
            echo json_encode($result, JSON_PRETTY_PRINT);
            break;

        case 'benchmark':
            run_memcached_benchmark($value);
            break;

        default:
            $result = array("message" => "tbd -list all commands here");
            echo json_encode($result, JSON_PRETTY_PRINT);
    }
}

function run_memcached_benchmark($value = 1) {
    $iteration = $value ?? 1;
    $result = ['iteration' => $iteration];
    $time_start_static = microtime(true);

    for ($i = 0; $i < $iteration; $i++) {
        if (mattjgagnon\MemcachedData::$data != null) {
            $cached_data = mattjgagnon\MemcachedData::$data;
        } else {
            //$time_start_memcached = microtime(true);
            mattjgagnon\MemcachedData::$data = Memcached::get_all_debug_items_memcache();
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
        if (isset($_SESSION['lg_debug_items'])) {
            $cached_data = $_SESSION['lg_debug_items'];
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

