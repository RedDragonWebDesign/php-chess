<?php

class ChessSquare {
	// Best to have 2 of these. $haystack[$needle] is 3x faster than array_search($needle, $haystack).
	// I tested it myself.
	const FILE_LETTERS_AND_NUMS = array(
		'a' => 1,
		'b' => 2,
		'c' => 3,
		'd' => 4,
		'e' => 5,
		'f' => 6,
		'g' => 7,
		'h' => 8
	);
	const FILE_NUMS_AND_LETTERS = array(
		1 => 'a',
		2 => 'b',
		3 => 'c',
		4 => 'd',
		5 => 'e',
		6 => 'f',
		7 => 'g',
		8 => 'h'
	);
	
	// I tried making these private and calculating them in getter methods, and
	// it's actually slower than calculating them in the constructor!
	public $rank;
	public $file;
	
	function __construct() {
		$args = func_get_args();
		
		// __construct($alphanumeric) - 1%
		if ( count($args) == 1 ) {
			$this->set_rankfile_using_alphanumeric($args[0]);
		// __construct($rank, $file) - 99%
		} else {
			$this->rank = $args[0];
			$this->file = $args[1];
		}
	}
	
	function calculate_alphanumeric_using_rankfile($rank, $file) {
		return self::FILE_NUMS_AND_LETTERS[$file] . $rank;
	}
	
	function set_rankfile_using_alphanumeric($alphanumeric) {
		$this->rank = (int)substr($alphanumeric, 1, 1);
		$this->file = self::FILE_LETTERS_AND_NUMS[ substr($alphanumeric, 0, 1) ];
	}
	
	function get_file_letter() {
		return self::FILE_NUMS_AND_LETTERS[$this->file];
	}
	
	function get_alphanumeric() {
		return $this->calculate_alphanumeric_using_rankfile($this->rank, $this->file);
	}
	
	function get_int() {
		// Primitive type int is faster than alphanumeric or ChessSquare for storing the
		// chess square in a list.
		return (int)($this->rank . $this->file);
	}
}
