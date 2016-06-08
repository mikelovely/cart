<?php

namespace Cart\Controllers;

use Slim\Router;
use Slim\Views\Twig;
use Cart\Models\Address;
use Cart\Models\Customer;
use Cart\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Cart\Basket\Basket;
use Cart\Validation\Contracts\ValidatorInterface;
use Cart\Validation\Forms\OrderForm;
use Braintree_Transaction;

class OrderController
{
	protected $basket;
	protected $router;
	protected $validator;

	public function __construct(Basket $basket, Router $router, ValidatorInterface $validator)
	{
		$this->basket = $basket;
		$this->router = $router;
		$this->validator = $validator;
	}

	public function index(Request $request, Response $response, Twig $view)
	{
		$this->basket->refresh();

		if(!$this->basket->subTotal()) {
			return $response->withRedirect($this->router->pathFor('cart.index'));
		}

		return $view->render($response, 'order/index.twig');
	}

	public function create(Request $request, Response $response, Address $address, Customer $customer)
	{
		$this->basket->refresh();

		if(!$this->basket->subTotal()) {
			return $response->withRedirect($this->router->pathFor('cart.index'));
		}

		if(!$request->getParam('payment_method_nonce')) {
			return $response->withRedirect($this->router->pathFor('order.index'));
		}

		$validation = $this->validator->validate($request, OrderForm::rules());

		if($validation->fails()) {
			return $response->withRedirect($this->router->pathFor('order.index'));
		}

		// todo: use service like Ramsey\Uuid\Uuid
		// or upgrade to php7 and use bin2hex(random_bytes(32));
		$hash = bin2hex(openssl_random_pseudo_bytes("32", $cstrong));

		$customer = $customer->firstOrCreate([
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
		]);

		$address = $address->firstOrCreate([
			'address1' => $request->getParam('address1'),
			'address2' => $request->getParam('address2'),
			'city' => $request->getParam('city'),
			'postal_code' => $request->getParam('postal_code'),
		]);

		$order = $customer->orders()->create([
			'hash' => $hash,
			'paid' => false,
			'total' => $this->basket->subTotal() + 5,
			'address_id' => $address->id,
		]);

		$order->products()->saveMany(
			$this->basket->all(),
			$this->getQuantities($this->basket->all())
		);

		$result = Braintree_Transaction::sale([
			'amount' => $this->basket->subTotal() + 5,
			'paymentMethodNonce' => $request->getParam('payment_method_nonce'),
			'options' => [
				'submitForSettlement' => True,
			],
		]);

		var_dump($result);
		exit;
	}

	protected function getQuantities($items)
	{
		$quantities = [];

		foreach ($items as $item) {
			$quantities[] = ['quantity' => $item->quantity];
		}

		return $quantities;
	}
}