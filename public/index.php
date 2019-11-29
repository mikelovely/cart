<?php

use Cart\App;
use Slim\Views\Twig;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new App();

//
$container = $app->getContainer();

// database
App::database($container);
App::braintree($container);

$app->group('', function () use ($app) {
    $app->get('/', ['Cart\Controllers\HomeController', 'index'])->setName('home');
    $app->get('/products/{slug}', ['Cart\Controllers\ProductController', 'get'])->setName('product.get');
    $app->get('/cart', ['Cart\Controllers\CartController', 'index'])->setName('cart.index');
    $app->get('/cart/add/{slug}/{quantity}', ['Cart\Controllers\CartController', 'add'])->setName('cart.add');
    $app->post('/cart/update/{slug}', ['Cart\Controllers\CartController', 'update'])->setName('cart.update');
    $app->get('/order', ['Cart\Controllers\OrderController', 'index'])->setName('order.index');
    $app->get('/order/{hash}', ['Cart\Controllers\OrderController', 'show'])->setName('order.show');
    $app->post('/order', ['Cart\Controllers\OrderController', 'create'])->setName('order.create');
    $app->get('/braintree/token', ['Cart\Controllers\BraintreeController', 'token'])->setName('braintree.token');
})->add(
    new \Cart\Middleware\ValidationErrorsMiddleware($container->get(Twig::class))
)->add(
    new \Cart\Middleware\OldInputMiddleware($container->get(Twig::class))
);

$app->run();
