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

    public static function database($container)
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

    public static function braintree($container)
    {
        \Braintree\Configuration::environment($container->get('Config')->get('braintree.environment'));
        \Braintree\Configuration::merchantId($container->get('Config')->get('braintree.merchant_id'));
        \Braintree\Configuration::publicKey($container->get('Config')->get('braintree.public_key'));
        \Braintree\Configuration::privateKey($container->get('Config')->get('braintree.private_key'));
    }
}