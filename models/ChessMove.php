<?php

class ChessMove {
	const PIECE_LETTERS = array(
		'pawn' => 'p',
		'knight' => 'n',
		'bishop' => 'b',
		'rook' => 'r',
		'queen' => 'q',
		'king' => 'k'
	);
	
	public $starting_square;
	public $ending_square;
	public $color;
	public $piece_type;
	public $capture;
	public $check;
	public $checkmate;
	public $promotion_piece_type; // Use the setter to change this. Keeping public so it can be read publicly.
	public $en_passant;
	public $disambiguation;
	public $castling;
	
	public $board;
	
	function __construct(
		$starting_square,
		$ending_square,
		$color,
		$piece_type,
		$capture,
		$old_board,
		$store_board = TRUE
	) {
		$this->starting_square = $starting_square;
		$this->ending_square = $ending_square;
		$this->color = $color;
		$this->piece_type = $piece_type;
		$this->capture = $capture;
		
		// These cases are rare. The data is passed in via $move->var = X instead of in the constructor.
		$this->disambiguation = '';
		$this->promotion_piece_type = NULL;
		$this->en_passant = FALSE;
		$this->check = FALSE;
		$this->checkmate = FALSE;
		
		// This case is set in the "if castling move rook" method. Set it FALSE for now.
		$this->castling = FALSE;		
		
		// Adding $store_board check sped up the code by 300ms
		if ( $store_board ) {
			if ( $this->starting_square ) {
				$this->board = clone $old_board;
				$this->board->make_move($starting_square, $ending_square);
				
				$this->possibly_remove_our_castling_privileges();
				$this->possibly_remove_enemy_castling_privileges();
				
				$this->if_castling_move_rook();
			} else { // Null move. Using it just to store a board.
				$this->board = clone $old_board;
			}
		}
	}
	
	function possibly_remove_our_castling_privileges() {
		// if our king or rook moves update the FEN to take away our castling privileges
		if ( $this->color == 'black' ) {
			if ( $this->piece_type == 'king' && $this->starting_square->alphanumeric == 'e8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == 'rook' && $this->starting_square->alphanumeric == 'a8' ) {
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == 'rook' && $this->starting_square->alphanumeric == 'h8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
			}
		} elseif ( $this->color == 'white' ) {
			if ( $this->piece_type == 'king' && $this->starting_square->alphanumeric == 'e1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == 'rook' && $this->starting_square->alphanumeric == 'a1' ) {
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == 'rook' && $this->starting_square->alphanumeric == 'h1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
			}
		}
	}
	
	function possibly_remove_enemy_castling_privileges() {
		// If an enemy rook is captured, update the FEN to take away enemy castling privileges.
		// We'll keep it simple. Anytime a piece moves into a corner square (a1, a8, h1, h8),
		// remove the other side's castling privileges.
		if ( $this->color == 'black' ) {
			if ( $this->ending_square->alphanumeric == 'a1' ) {
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->ending_square->alphanumeric == 'h1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
			}
		} elseif ( $this->color == 'white' ) {
			if ( $this->ending_square->alphanumeric == 'a8' ) {
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->ending_square->alphanumeric == 'h8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
			}
		}
	}
	
	function if_castling_move_rook() {
		
		// if castling, move the rook into the right place
		if ( $this->color == 'black' ) {
			if (
				$this->piece_type == 'king' &&
				$this->starting_square->alphanumeric == 'e8' &&
				$this->ending_square->alphanumeric == 'g8'
			) {
				$starting_square = new ChessSquare('h8');
				$ending_square = new ChessSquare('f8');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			} elseif (
				$this->piece_type == 'king' &&
				$this->starting_square->alphanumeric == 'e8' &&
				$this->ending_square->alphanumeric == 'c8'
			) {
				$starting_square = new ChessSquare('a8');
				$ending_square = new ChessSquare('d8');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			}
		} elseif ( $this->color == 'white' ) {
			if (
				$this->piece_type == 'king' &&
				$this->starting_square->alphanumeric == 'e1' &&
				$this->ending_square->alphanumeric == 'g1'
			) {
				$starting_square = new ChessSquare('h1');
				$ending_square = new ChessSquare('f1');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			} elseif (
				$this->piece_type == 'king' &&
				$this->starting_square->alphanumeric == 'e1' &&
				$this->ending_square->alphanumeric == 'c1'
			) {
				$starting_square = new ChessSquare('a1');
				$ending_square = new ChessSquare('d1');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			}
		}
	}
	
	// Do a deep clone. Needed for pawn promotion.
	function __clone() {
		$this->starting_square = clone $this->starting_square;
		$this->ending_square = clone $this->ending_square;
		if ( $this->board ) {
			$this->board = clone $this->board;
		}
	}
	
	function set_promotion_piece($piece_type) {
		// update the piece
		$rank = $this->ending_square->rank;
		$file = $this->ending_square->file;
		if ( $this->board ) {
			$this->board->board[$rank][$file]->type = $piece_type;
		}
		
		// update the notation
		$this->promotion_piece_type = $piece_type;
	}
	
	function get_notation() {
		$string = '';
		
		if (
			$this->starting_square->alphanumeric == 'e8' &&
			$this->ending_square->alphanumeric == 'g8' &&
			$this->piece_type == 'king' &&
			$this->color = 'black'
		) {
			$string .= 'O-O';
		} elseif (
			$this->starting_square->alphanumeric == 'e1' &&
			$this->ending_square->alphanumeric == 'g1' &&
			$this->piece_type == 'king' &&
			$this->color = 'white'
		) {
			$string .= 'O-O';
		} elseif (
			$this->starting_square->alphanumeric == 'e8' &&
			$this->ending_square->alphanumeric == 'c8' &&
			$this->piece_type == 'king' &&
			$this->color = 'black'
		) {
			$string .= 'O-O-O';
		} elseif (
			$this->starting_square->alphanumeric == 'e1' &&
			$this->ending_square->alphanumeric == 'c1' &&
			$this->piece_type == 'king' &&
			$this->color = 'white'
		) {
			$string .= 'O-O-O';
		} else {
			// type of piece
			if ( $this->piece_type == 'pawn' && $this->capture ) {
				$string .= substr($this->starting_square->alphanumeric, 0, 1);
			} elseif ( $this->piece_type != 'pawn' ) {
				$string .= strtoupper(self::PIECE_LETTERS[$this->piece_type]);
			}
			
			// disambiguation of rank/file/square
			$string .= $this->disambiguation;
			
			// capture?
			if ( $this->capture ) {
				$string .= 'x';
			}
			
			// destination square
			$string .= $this->ending_square->alphanumeric;
			
			// en passant
			if ( $this->en_passant ) {
				$string .= 'e.p.';
			}
			
			// pawn promotion
			if ( $this->promotion_piece_type == 'queen' ) {
				$string .= '=Q';
			} elseif ( $this->promotion_piece_type == 'rook' ) {
				$string .= '=R';
			} elseif ( $this->promotion_piece_type == 'bishop' ) {
				$string .= '=B';
			} elseif ( $this->promotion_piece_type == 'knight' ) {
				$string .= '=N';
			}
		}
		
		// check or checkmate
		if ( $this->checkmate ) {
			$string .= '#';
		} elseif ( $this->check ) {
			$string .= '+';
		}
		
		return $string;
	}
	
	function get_coordinate_notation() {
		// Automatically pick queen when drag and dropping.
		if (
			$this->promotion_piece_type == "rook" ||
			$this->promotion_piece_type == "bishop" ||
			$this->promotion_piece_type == "knight"
		) {
			return "";
		} else {		
			return $this->starting_square->alphanumeric . $this->ending_square->alphanumeric;
		}
	}
}
