<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;
use DateTimeZone;
use Exception;

final readonly class MemcachedAPI
{
    private const TIMEZONE_DEFAULT = 'America/New_York';
    private const MSG_LIST_COMMANDS = 'tbd -list all commands here';

    public function __construct(private array $argv, private int $argc, private array $get, private array $session,)
    {
    }

    /**
     * @throws Exception
     */
    public function memcached_api(): false|string
    {
        $datetime = new DateTime('now', new DateTimeZone(self::TIMEZONE_DEFAULT));
        $datetime_formatted = $datetime->format('F j, Y H:i:s');

        [$query, $value] = $this->get_query_value();

        $query_value_array = ['command' => ['query' => $query, 'value' => $value, 'hostname' => gethostname()]];

        switch ($query) {
            case 'stats':
                $command = new StatsCommand($query_value_array);
                $result = $command->execute();
                break;

            case 'set_all':
                $command = new SetAllCommand($query_value_array, $datetime_formatted);
                $result = $command->execute();
                break;

            case 'set':
                $command = new SetCommand($query_value_array, $datetime_formatted, $value);
                $result = $command->execute();
                break;

            case 'get':
                $command = new GetCommand($query_value_array, $value);
                $result = $command->execute();
                break;

            case 'get_all':
                $command = new GetAllCommand($query_value_array);
                $result = $command->execute();
                break;

            case 'get_keys':
                $command = new GetKeysCommand($query_value_array);
                $result = $command->execute();
                break;

            case 'db':
                $command = new DbCommand($query_value_array, $this->get['db']);
                $result = $command->execute();
                break;

            case 'flush':
                $command = new FlushCommand($query_value_array, $datetime);
                $result = $command->execute();
                break;

            case 'benchmark':
                $result = $this->run_memcached_benchmark($value);
                break;

            default:
                $result = ['message' => self::MSG_LIST_COMMANDS];
                break;
        }

        return json_encode($result, JSON_PRETTY_PRINT);
    }

    private function get_query_value(): array
    {
        $query = NULL;
        $value = NULL;

        $isCLI = (php_sapi_name() == 'cli');

        if ($isCLI && $this->argc > 1) {
            return $this->get_cli_values($query, $value);
        }

        return $this->get_params($query, $value);
    }

    private function get_cli_values(?string $query, ?string $value): array
    {
        $input_array = explode('=', $this->argv[1]);

        $query = $this->get_query_part($input_array, $query);

        $value = $this->get_value_part($input_array, $value);

        return [$query, $value];
    }

    private function get_query_part(array $input_array, ?string $query): mixed
    {
        if (count($input_array) > 0) {
            $query = $input_array[0];
        }

        return $query;
    }

    private function get_value_part(array $input_array, ?string $value): mixed
    {
        if (count($input_array) > 1) {
            $value = $input_array[1];
        }

        return $value;
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
