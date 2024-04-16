<?php

header('Content-Type: application/json');

use mattjgagnon\RefactoringPhp\refactors\MemcachedAPI;

$memcached = new MemcachedAPI($argv, $argc, $_GET, $_SESSION);
$memcached->memcached_api();
