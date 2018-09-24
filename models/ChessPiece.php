<?php

class ChessPiece
{
	public $color;
	public $type;
	public $square;
	
	const KING = 1;
	const QUEEN = 2;
	const ROOK = 3;
	const BISHOP = 4;
	const KNIGHT = 5;
	const PAWN = 6;
	
	const WHITE = 1;
	const BLACK = 2;
	
	const VALID_COLORS = array(
		self::WHITE,
		self::BLACK
	);
	const VALID_TYPES = array(
		self::KING,
		self::QUEEN,
		self::ROOK,
		self::BISHOP,
		self::KNIGHT,
		self::PAWN
	);
	
	const UNICODE_CHESS_PIECES = array(
		self::WHITE => array(
			self::KING => '&#9812;',
			self::QUEEN => '&#9813;',
			self::ROOK => '&#9814;',
			self::BISHOP => '&#9815;',
			self::KNIGHT => '&#9816;',
			self::PAWN => '&#9817;'
		),
		self::BLACK => array(
			self::KING => '&#9818;',
			self::QUEEN => '&#9819;',
			self::ROOK => '&#9820;',
			self::BISHOP => '&#9821;',
			self::KNIGHT => '&#9822;',
			self::PAWN => '&#9823;'
		)
	);
	const FEN_CHESS_PIECES = array(
		self::WHITE => array(
			self::KING => 'K',
			self::QUEEN => 'Q',
			self::ROOK => 'R',
			self::BISHOP => 'B',
			self::KNIGHT => 'N',
			self::PAWN => 'P'
		),
		self::BLACK => array(
			self::KING => 'k',
			self::QUEEN => 'q',
			self::ROOK => 'r',
			self::BISHOP => 'b',
			self::KNIGHT => 'n',
			self::PAWN => 'p'
		)
	);
	const PIECE_VALUES = array(
		self::PAWN => 1,
		self::KNIGHT => 3,
		self::BISHOP => 3,
		self::ROOK => 5,
		self::QUEEN => 9,
		self::KING => 0
	);
	const SIDE_VALUES = array(
		self::WHITE => 1,
		self::BLACK => -1
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
	}
	
	function __clone() {
		$this->square = clone $this->square;
	}
	
	function get_unicode_symbol()
	{
		return self::UNICODE_CHESS_PIECES[$this->color][$this->type];
	}
	
	function get_fen_symbol()
	{
		return self::FEN_CHESS_PIECES[$this->color][$this->type];
	}
	
	function on_rank($rank)
	{
		if ( $rank == $this->square->rank )	{
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function get_value() {
		return self::PIECE_VALUES[$this->type] * self::SIDE_VALUES[$this->color];
	}
}
