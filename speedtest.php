<?php

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

// ********************* LOOP 1 **************************

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

for ( $i = 1; $i <= 10000; $i++ ) {
	$a = FILE_NUMS_AND_LETTERS[4];
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

$total_time *= 1000;
$total_time = round($total_time);

echo "Loop 1 Time: $total_time ms<br />";


// ********************* LOOP 2 **************************

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

for ( $i = 1; $i <= 10000; $i++ ) {
	$a = array_search(4, FILE_LETTERS_AND_NUMS);
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish = $time;
$total_time = round(($finish - $start), 4);

$total_time *= 1000;
$total_time = round($total_time);

echo "Loop 2 Time: $total_time ms<br />";