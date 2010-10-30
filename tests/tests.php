<?php

include dirname(__FILE__).'/../chain.php';

class ChainTest extends PHPUnit_Framework_TestCase
{
	
    public function testArrayInterface() {
		$array = array(0, 1, 2, 3);
		$chain = Chain($array);
		
		$this->assertEquals( $array[0], $chain[0] );
		$this->assertEquals( $array[3], $chain[3] );
		
		$this->assertFalse( isset($chain[4]) );
		$this->assertTrue( isset($chain[2]) );
    }

	public function testIterable() {
		$array = array(0, 1, 2, 3);
		$sum = 0;
		foreach( $array as $value ) {
			$sum += $value;
		}
		$this->assertEquals( 6, $sum );
	}
	
	public function testMap() {
		$lower = array('one', 'two', 'three');
		$upper = Chain($lower)->map('strtoupper');
		
		$this->assertEquals( $upper[0], 'ONE' );
		$this->assertEquals( $upper[1], 'TWO' );
		$this->assertEquals( $upper[2], 'THREE' );
	}
	
	public function testReduce() {
		$numbers = array(1, 2, 3, 4);
		$sum = Chain($numbers)->reduce(function($sum, $value) {
			return $sum += $value;
		}, 0);
		
		$this->assertEquals( 10, $sum );
	}
	
	public function testReduceRight() {
		$letters = array('a', 'b', 'c');
		$chain = Chain($letters);
		
		$concat = function( $a, $b ) {
			return $a.$b;
		};
		
		$forwards = $chain->reduce($concat, '');
		$backwards = $chain->reduceRight($concat, '');
		
		$this->assertEquals( $forwards, 'abc' );
		$this->assertEquals( $backwards, 'cba' );
	}
	
	public function testDetect() {
		$chain = Chain(array(12, 14, 13, 16, 19));
		
		$odd = $chain->detect(function($val) {
			return $val & 1;
		});
		
		$this->assertEquals( $odd, 13 );
	}
	
	public function testSelect() {
		$chain = Chain(array(12, 14, 13, 16, 19));
		
		$odd = $chain->select(function($val) {
			return $val & 1;
		});
		
		$this->assertEquals( $odd, array(13, 19) );
	}
	
	public function testReject() {
		$chain = Chain(array(12, 14, 13, 16, 19));
		$even = $chain->reject(function($val) {
			return $val & 1;
		});
		
		$this->assertEquals( $even, array(12, 14, 16) );
	}
	
	public function testAll() {
		$chain_even = array(12, 14, 2080, 200);
		$chain_odd = array(113, 71, 1);
		$chain_mixed = array_merge($chain_even, $chain_odd);
		
		$odd = function($val) {
			return $val & 1;
		};
		$even = function($val) {
			return !($val & 1);
		};
		
		$chain_even_all_even = Chain($chain_even)->all($even);
		$chain_even_all_odd  = Chain($chain_even)->all($odd);
		
		$chain_odd_all_even = Chain($chain_odd)->all($even);
		$chain_odd_all_odd = Chain($chain_odd)->all($odd);
		
		$chain_mixed_all_even = Chain($chain_mixed)->all($even);
		$chain_mixed_all_odd = Chain($chain_mixed)->all($odd);
		
		$this->assertTrue( $chain_even_all_even );
		$this->assertTrue( $chain_odd_all_odd );
		
		$this->assertFalse( $chain_even_all_odd );
		$this->assertFalse( $chain_odd_all_even );
		$this->assertFalse( $chain_mixed_all_even );
		$this->assertFalse( $chain_mixed_all_odd );
	}
	
	function testAny() {
		$has_odd = Chain(array(4, 2, 1));
		$no_odd = Chain(array(4, 2, 6));
		
		$odd = function($val) {
			return $val & 1;
		};
		
		$this->assertTrue( $has_odd->any($odd) );
		$this->assertFalse( $no_odd->any($odd) );
	}
	
}