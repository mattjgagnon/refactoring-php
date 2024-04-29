<?php

namespace mattjgagnon\RefactoringPhp\refactors;

use DateTime;
use DateTimeZone;
use Exception;

final readonly class MemcachedAPI
{
    private const TIMEZONE_DEFAULT = 'America/New_York';

    public function __construct(private array $argv, private int $argc, private array $get, private array $session,)
    {
    }

    /**
     * @throws Exception
     */
    public function memcached_api(): false|string
    {
        $datetime = new DateTime('now', new DateTimeZone(self::TIMEZONE_DEFAULT));

        [$query, $value] = $this->get_query_value();

        $query_value_array = ['command' => ['query' => $query, 'value' => $value, 'hostname' => gethostname(),], 'datetime' => $datetime,];

        $command = match ($query) {
            'stats' => new StatsCommand($query_value_array),
            'set_all' => new SetAllCommand($query_value_array),
            'set' => new SetCommand($query_value_array),
            'get' => new GetCommand($query_value_array),
            'get_all' => new GetAllCommand($query_value_array),
            'get_keys' => new GetKeysCommand($query_value_array),
            'db' => new DbCommand($query_value_array),
            'flush' => new FlushCommand($query_value_array),
            'benchmark' => new BenchmarkCommand($query_value_array),
            default => new ListCommand($query_value_array),
        };

        $result = $command->execute();

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
            $value = $this->get['db'];

        } elseif (isset($this->get['flush'])) {
            $query = 'flush';

        } elseif (isset($this->get['benchmark'])) {
            $query = 'benchmark';
        }

        return [$query, $value];
    }
}
