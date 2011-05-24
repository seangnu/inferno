<?php
/**
 * Inferno
 *
 * Quick, lightweight and simple to use 
 * PHP unit testing library
 *
 * @author Jamie Rumbelow <http://jamierumbelow.net>
 * @version 0.1.0
 * @copyright Copyright (c)2011 Jamie Rumbelow
 * @package default
 **/

/* --------------------------------------------------------------
 * UNIT TESTER
 * ------------------------------------------------------------ */

class UnitTest {
	
	/* --------------------------------------------------------------
	 * VARIABLES
	 * ------------------------------------------------------------ */
	
	protected $results 			= array();
	protected $tests 			= array();
	protected $current_test 	= '';
	                        	
	protected $start_time 		= 0;
	protected $end_time 		= 0;
	protected $assertion_count	= 0;
	
	/* --------------------------------------------------------------
	 * AUTORUNNER
	 * ------------------------------------------------------------ */

	/**
	 * Run your tests!
	 */
	public static function test() {
		// Get all the declared classes
		$classes = get_declared_classes();
		
		// Loop through them and if they're subclasses of
		// UnitTest then instanciate and run them!
		foreach ($classes as $class) {
			if (is_subclass_of($class, 'UnitTest')) {
				$instance = new $class();
				$instance->run();
			}
		}
	}
	
	/* --------------------------------------------------------------
	 * GENERIC METHODS
	 * ------------------------------------------------------------ */
	
	public function __construct() {
		/* stub */
	}
	
	/**
	 * Record a success
	 */
	public function success() {
		$this->results[$this->current_test]['successes'][] = TRUE;
	}
	
	/**
	 * Record a failure
	 */
	public function failure($message) {
		$this->results[$this->current_test]['failures'][] = $message;
	}
	
	/* --------------------------------------------------------------
	 * UNIT TESTING METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Assert that an expression meets TRUE boolean. The
	 * base for all the other assertions
	 */
	public function assert($expression, $message = '') {
		if ((bool)($expression) == TRUE) {
			$this->success();
		} else {
			$message = ($message) ? $message : (string)($expression) . " did not equate to TRUE";
			throw new UnitTestFailure($message);
		}
	}
	
	public function assert_equal($one, $two, $message = '') {
		$message = ($message) ? $message : "$one did not equal $two";
		$this->assert(($one == $two), $message);
	}
	
	/* --------------------------------------------------------------
	 * TEST RUNNING METHODS
	 * ------------------------------------------------------------ */
	
	public function run() {
		$this->get_tests();
		$this->run_tests();
		$this->print_results();
	}
	
	/**
	 * Loop through all the methods that begin with
	 * test_ and add them to the $this->tests array.
	 */
	public function get_tests() {
		$methods = get_class_methods($this);
		
		foreach ($methods as $method) {
			if (substr($method, 0, 5) == 'test_') {
				$this->tests[] = $method;
			}
		}
	}
	
	/**
	 * Run each test
	 */
	public function run_tests() {
		$this->start_time = microtime(TRUE);
		
		foreach ($this->tests as $test) {
			$this->current_test = $test;
			
			try {
				call_user_func_array(array($this, $test), array());
			} catch (Exception $e) {
				if (get_class($e) == 'UnitTestFailure') {
					$this->failure($e->getMessage());
				}
			}
		}
		
		$this->end_time = microtime(TRUE);
	}
	
	/**
	 * Loop through the test results and output them
	 * to the console!
	 */
	public function print_results() {
		$failures = array();
		$errors = array();
		$good = TRUE;
		
		// Print out the running status of each method.
		foreach ($this->results as $unit_test => $results) {
			foreach ($results as $result => $values) {
				foreach ($values as $value) {
					$this->assertion_count++;
					
					switch ($result) {
						case 'failures': echo('✘ '); $failures[$unit_test][] = $value; break;
						case 'errors': echo('! '); $errors[$unit_test][] = $value; break;
					
						default:
						case 'successes': echo('✓ '); break;
					}
				}
			}
		}
		
		echo("\n----------------------------------\n\n");
		
		// Do we have any failures?
		if ($failures) {
			$good = FALSE;
			
			foreach ($failures as $unit_test => $messages) {
				echo("Failures!\n");
				echo("=========\n\n");
				
				echo($unit_test . "():\n");
				
				foreach ($messages as $message) {
					echo("\t- " . $message);
				}
				
				echo("\n");
			}
			
			echo("\n----------------------------------\n\n");
		}
		
		// Do we have any failures?
		if ($errors) {
			$good = FALSE;
			
			foreach ($errors as $unit_test => $messages) {
				echo("Errors!\n");
				echo("=======\n\n");
				
				echo($unit_test . "():\n");
				
				foreach ($messages as $message) {
					echo("\t- " . $message);
				}
			}
			
			echo("\n----------------------------------\n\n");
		}
		
		// Good or bad?
		if ($good) {
			echo("\033[0;32mCool! All your tests ran perfectly.\033[0m\n");
		} else {
			echo("\033[0;31mNot so cool :( there was a problem running your tests!\033[0m\n");
		}
		
		// Finally, test stats
		echo("Ran " . 
			 $this->assertion_count . 
			 " assertion(s) in " . 
			 number_format(($this->end_time - $this->start_time), 6) . 
			 " seconds");
	}
}

/* --------------------------------------------------------------
 * EXCEPTIONS
 * ------------------------------------------------------------ */

class UnitTestFailure extends Exception { }