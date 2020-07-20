<?php

	// Example usage: html_var_export($limit, '$limit');
	function html_var_export($var, bool $var_name = NULL): void
	{
		$output = '';
		
		if ( $var_name )
		{
			$output .= $var_name . ' = ';
		}
		
		$output .= nl2br_and_nbsp(var_export($var, TRUE)) . "<br><br>";
		
		echo $output;
	}
	
	function nl2br_and_nbsp(string $string): string
	{
		$string = nl2br($string);
		
		$string = nbsp($string);
		
		return $string;
	}
	
	function nbsp(string $string): string
	{
		preg_replace('/\t/', '&nbsp;&nbsp;&nbsp;&nbsp;', $string);
		
		// replace more than 1 space in a row with &nbsp;
		$string = preg_replace('/  /m', '&nbsp;&nbsp;', $string);
		$string = preg_replace('/ &nbsp;/m', '&nbsp;&nbsp;', $string);
		$string = preg_replace('/&nbsp; /m', '&nbsp;&nbsp;', $string);
		
		if ( $string == ' ' )
		{
			$string = '&nbsp;';
		}
		
		return $string;
	}
	
	function print_var_name($var): ?string {
		foreach($GLOBALS as $var_name => $value) {
			if ($value === $var) {
				return $var_name;
			}
		}

		return NULL;
	}