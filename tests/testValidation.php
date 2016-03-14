<?php
/**
 * @author Mateusz Pietraszewski
 *
 * @version 1.0.0
 * 
 * @param $this is a class variable
 */
require_once('../SimpleTest/autorun.php');
class testValidationClass extends UnitTestCase{
	
	private $validation;
	public function setUp(){
		require_once('../app/validation.php');
		$this->validation = new validation();
	}
	public function tearDown(){
		$this->validation = NULL;
	}
	
	function testEmailValidationWithValidEmail(){
		
		// Check for valid email
		$this->assertTrue($this->validation->isEmailValid("matixpietras@gmail.com"));
		
		// Check for invald email using various characters	
		$this->assertFalse($this->validation->isEmailValid("x#'#;#';2#3';42#asdfasdga@.asgdga']['"));
		
		// Check for invalid email using only numbers
		$this->assertFalse($this->validation->isEmailValid("9080253752382573"));
		
		// Check if passing an object is a valid email
		$this->assertFalse($this->validation->isEmailValid($this->validation));
		
		// Check if apssing an array is valid email
		$this->assertFalse($this->validation->isEmailValid(['adg#',67,'77jdf']));
	}
	
	function testNumberInRange(){
		
		// Check if the 5 is between 1 and 10
		$this->assertTrue($this->validation->isNumberInRangeValid(5, 1, 10));
		
		// Check if the letter a is between 1 and 10
		$this->assertFalse($this->validation->isNumberInRangeValid('a', 1, 10));
		
		// Check if the letter c is between a and g
		$this->assertFalse($this->validation->isNumberInRangeValid('c', 'a', 'g'));
		
		// Check if 0 is between -10 and 10
		$this->assertTrue($this->validation->isNumberInRangeValid(0, -10, 10));
		
		// Check if 0 is btween 10 and -10 (reversed)
		$this->assertFalse($this->validation->isNumberInRangeValid(0, 10, -10));
		
		// Check if function works with floats
		$this->assertTrue($this->validation->isNumberInRangeValid(5.6, -45.7, 67.9));
		
		// Check if this object is between 0 and an array
		$this->assertFalse($this->validation->isNumberInRangeValid($this->validation, 0, ['adg#',67,'77jdf']));
	}
	
	function testLengthOfString(){
		
		// Check if string is equal to the number
		$this->assertTrue($this->validation->isLengthStringValid("nice", 4));
		
		// Check in wrong order
		$this->assertFalse($this->validation->isLengthStringValid(4, "nice"));
		
		// Check if the string has less characters than number
		$this->assertTrue($this->validation->isLengthStringValid("nice", 8));
		
		// Check if the string has less character then number
		$this->assertFalse($this->validation->isLengthStringValid("nice", 3));
		
		// Check if the string has less characters than another string
		$this->assertFalse($this->validation->isLengthStringValid("nice", "'#';#'';5235"));
		
		// Check if the string hass less character than a object
		$this->assertFalse($this->validation->isLengthStringValid("nice", $this->validation));
		
		// Check if the array is the same legth as a number
		$this->assertFalse($this->validation->isLengthStringValid(["nice","67ol"], 2));
		
		$this->assertFalse($this->validation->isLengthStringValid(array(), 2));
		$this->assertFalse($this->validation->isLengthStringValid(null, array()));
		$this->assertFalse($this->validation->isLengthStringValid(new validation(), null));
	}
}

?>