<?php

namespace Cart;

use function DI\get;
use Cart\Basket\Basket;
use Cart\Models\Address;
use Cart\Models\Customer;
use Cart\Models\Order;
use Cart\Models\Payment;
use Cart\Models\Product;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Support\Storage\SessionStorage;
use Cart\Validation\Contracts\ValidatorInterface;
use Cart\Validation\Validator;
use DI\Bridge\Slim\App as DiBridge;
use DI\ContainerBuilder;
use Illuminate\Database\Capsule\Manager as Capsule;
use Interop\Container\ContainerInterface;
use Noodlehaus\Config;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

class App extends DIBridge
{
    public static function make()
    {
        $app = new self();

        $app->database($app->getContainer());
        $app->braintree($app->getContainer());
        $app->routes($app);

        return $app;
    }

	protected function configureContainer(ContainerBuilder $builder)
	{
		$builder->addDefinitions([
			'settings.displayErrorDetails' => true,
		]);

        $this->config($builder);
        $this->models($builder);
        $this->services($builder);
	}

    private function config($builder)
    {
        $builder->addDefinitions([
            'Config' => new Config(__DIR__ .  '/../config.json'),
        ]);
    }

    private function models($builder)
    {
        $builder->addDefinitions([
            Product::class => function () {
                return new Product();
            },
        ]);

        $builder->addDefinitions([
            Product::class => function () {
                return new Product();
            },
        ]);

        $builder->addDefinitions([
            Customer::class => function () {
                return new Customer();
            },
        ]);

        $builder->addDefinitions([
            Address::class => function () {
                return new Address();
            },
        ]);

        $builder->addDefinitions([
            Order::class => function () {
                return new Order();
            },
        ]);

        $builder->addDefinitions([
            Payment::class => function () {
                return new Payment();
            },
        ]);
    }

    private function services($builder)
    {
        $builder->addDefinitions([
            Slim\Router::class => get(Slim\Router::class),
        ]);

        $builder->addDefinitions([
            ValidatorInterface::class => function () {
                return new Validator();
            },
        ]);

        $builder->addDefinitions([
            StorageInterface::class => function() {
                return new SessionStorage('cart');
            },
        ]);

        $builder->addDefinitions([
            Twig::class => function ($c) {
                $twig = new Twig(__DIR__ . '/../resources/views', [
                    'cache' => false,
                ]);

                $twig->addExtension(new TwigExtension(
                    $c->get('router'),
                    $c->get('request')->getUri()
                ));

                $twig->getEnvironment()->addGlobal('basket', $c->get(Basket::class));

                return $twig;
            },
        ]);

        $builder->addDefinitions([
            Basket::class => function ($c) {
                return new Basket(
                    $c->get(SessionStorage::class),
                    $c->get(Product::class)
                );
            }
        ]);
    }

    private function database($container)
    {
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => $container->get('Config')->get('database.mysql.driver'),
            'host' => $container->get('Config')->get('database.mysql.host'),
            'database' => $container->get('Config')->get('database.mysql.database'),
            'username' => $container->get('Config')->get('database.mysql.username'),
            'password' => $container->get('Config')->get('database.mysql.password'),
            'port' => $container->get('Config')->get('database.mysql.port'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    private function braintree($container)
    {
        \Braintree\Configuration::environment($container->get('Config')->get('braintree.environment'));
        \Braintree\Configuration::merchantId($container->get('Config')->get('braintree.merchant_id'));
        \Braintree\Configuration::publicKey($container->get('Config')->get('braintree.public_key'));
        \Braintree\Configuration::privateKey($container->get('Config')->get('braintree.private_key'));
    }

    private function routes($app)
    {
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
            new \Cart\Middleware\ValidationErrorsMiddleware($app->getContainer()->get(Twig::class))
        )->add(
            new \Cart\Middleware\OldInputMiddleware($app->getContainer()->get(Twig::class))
        );
    }
}
