<?php

header('Content-Type: application/json');

use mattjgagnon\RefactoringPhp\refactors\MemcachedAPI;

$arguments = $argv;
$arguments_count = $argc;
$memcached = new MemcachedAPI($arguments, $arguments_count);
$memcached->memcached_api();
