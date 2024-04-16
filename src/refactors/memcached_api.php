<?php

header('Content-Type: application/json');

use mattjgagnon\RefactoringPhp\refactors\MemcachedAPI;

$memcached = new MemcachedAPI($argv, $argc);
$memcached->memcached_api();
