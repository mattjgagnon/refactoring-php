<?php

header('Content-Type: application/json');

use mattjgagnon\MemcachedAPI;

$arguments = $argv;
$arguments_count = $argc;
MemcachedAPI::memcached_api($arguments, $arguments_count);

