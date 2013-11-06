<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 */
class SampleTest extends Kohana_Unittest_TestCase
{

	public function test_add() {
		$this->assertEquals(2, 1 + 1);
	}
}
