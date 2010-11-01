<?php

class Chain implements ArrayAccess, Iterator {
	
	private $data;
	
	static $aliases = array(
					'inject' => 'reduce',
					'foldl' => 'reduce',
					
					'foldr' => 'reduceRight',
					
					'filter' => 'select',
					
					'every' => 'all',
					'some' => 'any',
					'contains' => 'include'
				);
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	/*********************
	 * Functional Wrappers
	 ********************/
	
	private function map($function) {
		$new = array();
		foreach( $this->data as $key => $value ) {
			$new[$key] = call_user_func($function, $value);
		}
		return Chain($new);
	}
	
	private function reduce($function, $memo) {
		foreach( $this->data as $value ) {
			$memo = call_user_func($function, $memo, $value);
		}
		return Chain($memo);
	}
	
	private function reduceRight($function, $memo) {
		$this->data = array_reverse($this->data);
		$return = $this->reduce($function, $memo);
		$this->data = array_reverse($this->data);
		return Chain($return);
	}
	
	private function detect($function) {
		foreach( $this->data as $value ) {
			if( $function($value) )
				return $value;
		}
		return false;
	}
	
	private function select($function) {
		$return = array();
		foreach( $this->data as $value ) {
			if( $function($value) )
				$return[] = $value;
		}
		return Chain($return);
	}
	
	private function reject($function) {
		$return = array();
		foreach( $this->data as $value ) {
			if( !$function($value) )
				$return[] = $value;
		}
		return Chain($return);
	}
	
	private function all($function) {
		$rejected = $this->reject($function)->toArray();
		return !$rejected;
	}
	
	private function any($function) {
		$found = (bool) $this->detect($function);
		return $found;
	}
	
	private function pluck($key=false) {
		$return = array();
		foreach( $this->data as $value ) {
			if( is_array($value) && isset($value[$key]) )
				$return[] = $value[$key];
			else if( is_object($value) && isset($value->$key) )
				$return[] = $value->$key;
		}
		return Chain($return);
	}
	
	private function compact() {
		// Removes all false evaluating items
		$compacted = array();
		foreach( $this->data as $key => $value ) {
			if( $value )
				$compacted[] = $value;
		}
		return Chain($compacted);
	}
	
	private function flatten() {
		
	}
	
	/*******************
	 * Array Interface
	 ******************/
	function offsetExists($offset) {
		return isset( $this->data[$offset] );
	}
	
	function offsetGet($offset) {
		return $this->data[$offset];
	}
	
	function offsetSet($offset, $value) {
		return $this->data[$offset] = $value;
	}
	
	function offsetUnset($offset) {
		unset($this->data[$offset]);
	}
	
	/**********************
	 * Iterator Interface
	 *********************/
	private $key = 0;
	function current() {
		return $this->data[$this->key];
	}
	function key() {
		return $this->key;
	}
	function next() {
		return $this->key++;
	}
	function rewind() {
		return $this->key = 0;
	}
	function valid() {
		return isset($this->data[$this->key]);
	}
	
	/**********************
	 * Casting Functions
	 *********************/
	
	public function toArray() {
		return (array) $this->data;
	}
	
	/**********************
	 * Magic Methods
	 *********************/
	
	function __call($method, $arguments) {		
		$method = strtolower($method);
		
		if( isset(self::$aliases[$method]) )
			$method = self::$aliases[$method];
			
		if( method_exists($this, $method) )
			return call_user_func_array(array($this, $method), $arguments);
		else
			throw new Exception('Method '.$method.' does not exist.');
	}
	
	public static function __callStatic($method, $arguments) {
		$data = array_shift($arguments);
		$chain = Chain($data);
		$return = call_user_func_array( array($chain, $method), $arguments );
		return ($return instanceof Chain) ? $return->toArray() : $return;
	}
	
}

function Chain($data) {
	if( is_array($data) || $data instanceof Traversable )
		return new Chain($data);
	else
		return $data;
}