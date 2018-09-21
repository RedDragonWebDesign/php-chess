<?php

Class Timer {
	public $start_timestamp;
	
	function __construct() {
		$this->start_timestamp = self::get_current_timestamp();
	}
	
	function get_duration_in_milliseconds() {
		$current_timestamp = self::get_current_timestamp();
		
		$total_time = round(($current_timestamp - $this->start_timestamp), 4);
		
		$total_time *= 1000;
		$total_time = round($total_time);
		
		return $total_time;
	}
	
	static function get_current_timestamp() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		return $time;
	}
}

const TIMES_TO_LOOP = 1000000;

// ********************* LOOP 1 **************************

$timer = new Timer();

Class StaticClass {
	function static_method($a) {
		return $a * 2;
	}
}

for ( $i = 1; $i <= TIMES_TO_LOOP; $i++ ) {
	$c = StaticClass::static_method($i);
}

$total_time = $timer->get_duration_in_milliseconds();

echo "Loop 1 Time: $total_time ms<br />";


// ********************* LOOP 2 **************************

$timer = new Timer();

Class NormalClass {
	function normal_method($a) {
		return $a * 2;
	}
}

$b = new NormalClass();

for ( $i = 1; $i <= TIMES_TO_LOOP; $i++ ) {
	$c = $b->normal_method($i);
}

$total_time = $timer->get_duration_in_milliseconds();

echo "Loop 2 Time: $total_time ms<br />";
