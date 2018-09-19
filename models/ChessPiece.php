<?php

class ChessPiece
{
	public $value;
	public $color;
	public $type;
	public $square;
	
	const VALID_COLORS = array('white', 'black');
	const VALID_TYPES = array('pawn', 'knight', 'bishop', 'rook', 'queen', 'king');
	const UNICODE_CHESS_PIECES = array(
		'white_king' => '&#9812;',
		'white_queen' => '&#9813;',
		'white_rook' => '&#9814;',
		'white_bishop' => '&#9815;',
		'white_knight' => '&#9816;',
		'white_pawn' => '&#9817;',
		'black_king' => '&#9818;',
		'black_queen' => '&#9819;',
		'black_rook' => '&#9820;',
		'black_bishop' => '&#9821;',
		'black_knight' => '&#9822;',
		'black_pawn' => '&#9823;'
	);
	const FEN_CHESS_PIECES = array(
		'white_king' => 'K',
		'white_queen' => 'Q',
		'white_rook' => 'R',
		'white_bishop' => 'B',
		'white_knight' => 'N',
		'white_pawn' => 'P',
		'black_king' => 'k',
		'black_queen' => 'q',
		'black_rook' => 'r',
		'black_bishop' => 'b',
		'black_knight' => 'n',
		'black_pawn' => 'p'
	);
	const PIECE_VALUES = array(
		'pawn' => 1,
		'knight' => 3,
		'bishop' => 3,
		'rook' => 5,
		'queen' => 9,
		'king' => 0
	);
	const SIDE_VALUES = array(
		'white' => 1,
		'black' => -1
	);
	
	function __construct($color, $square_string, $type) {
		if ( in_array($color, self::VALID_COLORS) ) {
			$this->color = $color;
		} else {
			throw new Exception('Invalid ChessPiece Color');
		}
		
		$this->square = new ChessSquare($square_string);
		
		if ( in_array($type, self::VALID_TYPES) ) {
			$this->type = $type;
		} else {
			throw new Exception('Invalid ChessPiece Type');
		}
		
		$this->value = self::PIECE_VALUES[$type] * self::SIDE_VALUES[$color];
	}
	
	function __clone() {
		$this->square = clone $this->square;
	}
	
	function get_unicode_symbol()
	{
		$dictionary_key = $this->color . '_' . $this->type;
		
		return self::UNICODE_CHESS_PIECES[$dictionary_key];
	}
	
	function get_fen_symbol()
	{
		$dictionary_key = $this->color . '_' . $this->type;
		
		return self::FEN_CHESS_PIECES[$dictionary_key];
	}
	
	function on_rank($rank)
	{
		if ( $rank == $this->square->rank )	{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
