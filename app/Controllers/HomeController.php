<?php

namespace Cart\Controllers;

use Slim\Views\Twig;
use Cart\Models\Product;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController
{
	public function index(Request $request, Response $response, Twig $view, Product $product)
	{
		$products = Product::get();

		var_dump($products);
		exit;

		return $view->render($response, 'home.twig');
	}
}