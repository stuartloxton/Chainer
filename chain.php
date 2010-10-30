<?php

class Chain implements ArrayAccess, Iterator {
	
	private $data;
	
	public function __construct($data) {
		$this->data = $data;
	}
	
	/*********************
	 * Functional Wrappers
	 ********************/
	
	function map($function) {
		foreach( $this->data as $key => &$value ) {
			$value = call_user_func($function, $value);
		}
		return $this;
	}
	
	function reduce($function, $memo) {
		foreach( $this->data as $value ) {
			$memo = call_user_func($function, $memo, $value);
		}
		return $memo;
	}
	
	function reduceRight($function, $memo) {
		$this->data = array_reverse($this->data);
		$return = $this->reduce($function, $memo);
		$this->data = array_reverse($this->data);
		return $return;
	}
	
	function detect($function) {
		foreach( $this->data as $value ) {
			if( $function($value) )
				return $value;
		}
		return false;
	}
	
	function select($function) {
		$return = array();
		foreach( $this->data as $value ) {
			if( $function($value) )
				$return[] = $value;
		}
		return $return;
	}
	
	function reject($function) {
		$return = array();
		foreach( $this->data as $value ) {
			if( !$function($value) )
				$return[] = $value;
		}
		return $return;
	}
	
	function all($function) {
		$rejected = $this->reject($function);
		return !$rejected;
	}
	
	function any($function) {
		$found = (bool) $this->detect($function);
		return $found;
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
	
}

function Chain($data) {
	return new Chain($data);
}

$data = array(0, 1, 2, 3, 4);
print_r(Chain($data)->map(function($x) { return $x * $x; })->reduce(function($sum, $x) { return $sum + $x; }, 0));
