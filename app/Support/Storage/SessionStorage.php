<?php

namespace Cart\Support\Storage;

use Countable;
use Cart\Support\Storage\Contracts\StorageInterface;

class SessionStorage implements StorageInterface, Countable
{
	protected $basket;

	public function __construct($basket = 'default')
	{
		if(!isset($_SESSION[$basket])) {
			$_SESSION[$basket] = [];
		}

		$this->basket = $basket;
	}

	public function set($index, $value)
	{
		$_SESSION[$this->basket][$index] = $value;
	}

	public function get($index)
	{
		if(!$this->exists($index)) {
			return null;
		}

		return $_SESSION[$this->basket][$index];
	}

	public function exists($index)
	{
		return isset($_SESSION[$this->basket][$index]);
	}

	public function all()
	{
		return $_SESSION[$this->basket];
	}

	public function unset($index)
	{
		if($this->exists($index)) {
			unset($_SESSION[$this->basket][$index]);
		}
	}

	public function clear()
	{
		unset($_SESSION[$this->basket]);
	}

	public function count()
	{
		return count($this->all());
	}
}
