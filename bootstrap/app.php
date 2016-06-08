<?php

use Cart\App;
use Slim\Views\Twig;
use Illuminate\Database\Capsule\Manager as Capsule;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new App;

$container = $app->getContainer();

$capsule = new Capsule;
$capsule->addConnection([
	'driver' => 'mysql',
	'host' => 'localhost',
	'database' => 'cart',
	'username' => 'homestead',
	'password' => 'secret',
	'charset' => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// todo: use library like https://github.com/vlucas/phpdotenv instead
Braintree_Configuration::environment('sandbox');
Braintree_Configuration::merchantId('3kdf4v3pz79xyj8w');
Braintree_Configuration::publicKey('sq3pghjv8m4bq3q8');
Braintree_Configuration::privateKey('5882edec87c405a4b0224275426b71fa');

require __DIR__ . '/../app/routes.php';

$app->add(new \Cart\Middleware\ValidationErrorsMiddleware($container->get(Twig::class)));
$app->add(new \Cart\Middleware\OldInputMiddleware($container->get(Twig::class)));
