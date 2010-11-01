<?php

error_reporting(E_ALL | E_STRICT);

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
	
	public function testMapEffect() {
		$numbers = Chain(array(1,2,3));
		$squared = $numbers->map(function($x) { return $x * $x; });
		
		$this->assertEquals( array(1,2,3), $numbers->toArray() );
		$this->assertEquals( array(1, 4, 9), $squared->toArray() );
		
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
		})->toArray();
		
		$this->assertEquals( $odd, array(13, 19) );
	}
	
	public function testReject() {
		$chain = Chain(array(12, 14, 13, 16, 19));
		$even = $chain->reject(function($val) {
			return $val & 1;
		})->toArray();
		
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
	
	function testPluck() {
		$person_1 = array('Name' => 'Tom');
		$person_2 = array('Name' => 'Nemo', 'Type' => 'Fish');
		$person_3 = array('Type' => 'Unknown');
		
		$array_data = array($person_1, $person_2, $person_3);
		$object_data = array( (object) $person_1, (object) $person_2, (object) $person_3);
		
		$array_names = Chain($array_data)->pluck('Name');
		$object_names = Chain($object_data)->pluck('Name');
		
		// Test Array + Object access
		$this->assertEquals( 'Tom', $array_names[0] );
		$this->assertEquals( 'Nemo', $array_names[1] );
		$this->assertEquals( 'Tom', $object_names[0] );
		$this->assertEquals( 'Nemo', $object_names[1] );
		
		// Test Raw toArray
		$this->assertEquals( array('Tom', 'Nemo'), $array_names->toArray() );
		$this->assertEquals( array('Tom', 'Nemo'), $object_names->toArray() );
		
		// test ignores blank names
		$this->assertFalse( isset($array_names[2]) );
		$this->assertFalse( isset($object_names[2]) );
		
		// Test chainability
		$this->assertEquals( array('TOM', 'NEMO'), $array_names->map('strtoupper')->toArray() );
	}
	
	function testCompact() {
		$array = array(1, 0, '', null, 4, 7, 8);
		$chain = Chain($array);
		
		$this->assertEquals( array(1, 4, 7, 8), $chain->compact()->toArray() );
		$this->assertEquals( 4, $chain->compact()->offsetGet(1) );
	}
	
	
	
	
	
	
	/*******************
	 * UTILITY TESTS
	 ******************/
	
	function testAliases() {
		$chain = Chain(array(1, 2, 3, 4));
		
		$reduced = $chain->reduce(function($sum, $x) { 
			return $sum + $x;
		}, 0);
		
		$folded_left = $chain->foldl(function($sum, $x) { 
			return $sum + $x;
		}, 0);
		
		$this->assertEquals( $reduced, $folded_left );
		
	}
	
	function testStatic() {
		$static = Chain::map(array(1,2,3), function($x) {
			return $x * $x;
		});
		
		$method = Chain(array(1,2,3))->map(function($x) {
			return $x * $x;
		})->toArray();
		
		$this->assertEquals( $static, $method );
	}
	
	
	
}