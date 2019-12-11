<?php

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = \Cart\App::make();
$app->run();
