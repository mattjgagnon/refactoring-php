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

        [
            $query,
            $value,
        ] = $this->get_query_value();

        $query_value_array = [
            'command' => [
                'query' => $query,
                'value' => $value,
                'hostname' => gethostname(),
            ],
            'datetime' => $datetime,
        ];
        $command_class_name = $this->get_class_name($query);
        $command = new $command_class_name($query_value_array);
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

        return [
            $query,
            $value,
        ];
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
        $map = [
            'stats' => 'stats',
            'set_all' => 'set_all',
            'set' => 'set',
            'get' => 'get',
            'get_all' => 'get_all',
            'get_keys' => 'get_keys',
            'db' => 'db',
            'flush' => 'flush',
            'benchmark' => 'benchmark',
        ];

        foreach ($map as $key => $mappedQuery) {
            if (isset($this->get[$key])) {
                $query = $mappedQuery;
                $value = $this->get[$key] ?? $value;
                break;
            }
        }

        return [
            $query,
            $value,
        ];
    }

    private function get_class_name(mixed $query): string
    {
        $name = str_replace('_', ' ', $query);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);

        if (empty($name)) {
            $name = 'List';
        }

        return __NAMESPACE__ . '\\' . $name . 'Command';
    }
}
