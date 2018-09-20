<?php

class ChessMove {
	const PIECE_LETTERS = array(
		ChessPiece::PAWN => 'p',
		ChessPiece::KNIGHT => 'n',
		ChessPiece::BISHOP => 'b',
		ChessPiece::ROOK => 'r',
		ChessPiece::QUEEN => 'q',
		ChessPiece::KING => 'k'
	);
	
	public $starting_square;
	public $ending_square;
	public $color;
	public $piece_type;
	public $capture;
	public $check = FALSE;
	public $checkmate = FALSE;
	public $promotion_piece_type = NULL; // Use the setter to change this. Keeping public so it can be read publicly.
	public $en_passant = FALSE;
	public $disambiguation = '';
	public $castling = FALSE;
	
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
		
		// Adding $store_board sped up the code by 300ms
		if ( $store_board ) {
			$this->board = clone $old_board;
			
			// Perft uses an empty move to store a board. If not empty move, modify the board.
			if ( $this->starting_square ) {
				$this->board->make_move($starting_square, $ending_square);
				
				$this->possibly_remove_our_castling_privileges();
				$this->possibly_remove_enemy_castling_privileges();
				
				$this->if_castling_move_rook();
			}
		}
	}
	
	function possibly_remove_our_castling_privileges() {
		// if our king or rook moves update the FEN to take away our castling privileges
		if ( $this->color == ChessPiece::BLACK ) {
			if ( $this->piece_type == ChessPiece::KING && $this->starting_square->get_alphanumeric() == 'e8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == ChessPiece::ROOK && $this->starting_square->get_alphanumeric() == 'a8' ) {
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == ChessPiece::ROOK && $this->starting_square->get_alphanumeric() == 'h8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
			}
		} elseif ( $this->color == ChessPiece::WHITE ) {
			if ( $this->piece_type == ChessPiece::KING && $this->starting_square->get_alphanumeric() == 'e1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == ChessPiece::ROOK && $this->starting_square->get_alphanumeric() == 'a1' ) {
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->piece_type == ChessPiece::ROOK && $this->starting_square->get_alphanumeric() == 'h1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
			}
		}
	}
	
	function possibly_remove_enemy_castling_privileges() {
		// If an enemy rook is captured, update the FEN to take away enemy castling privileges.
		// We'll keep it simple. Anytime a piece moves into a corner square (a1, a8, h1, h8),
		// remove the other side's castling privileges.
		if ( $this->color == ChessPiece::BLACK ) {
			if ( $this->ending_square->get_alphanumeric() == 'a1' ) {
				$this->board->castling['white_can_castle_queenside'] = FALSE;
			} elseif ( $this->ending_square->get_alphanumeric() == 'h1' ) {
				$this->board->castling['white_can_castle_kingside'] = FALSE;
			}
		} elseif ( $this->color == ChessPiece::WHITE ) {
			if ( $this->ending_square->get_alphanumeric() == 'a8' ) {
				$this->board->castling['black_can_castle_queenside'] = FALSE;
			} elseif ( $this->ending_square->get_alphanumeric() == 'h8' ) {
				$this->board->castling['black_can_castle_kingside'] = FALSE;
			}
		}
	}
	
	function if_castling_move_rook() {
		
		// if castling, move the rook into the right place
		if ( $this->color == ChessPiece::BLACK ) {
			if (
				$this->piece_type == ChessPiece::KING &&
				$this->starting_square->get_alphanumeric() == 'e8' &&
				$this->ending_square->get_alphanumeric() == 'g8'
			) {
				$starting_square = new ChessSquare('h8');
				$ending_square = new ChessSquare('f8');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			} elseif (
				$this->piece_type == ChessPiece::KING &&
				$this->starting_square->get_alphanumeric() == 'e8' &&
				$this->ending_square->get_alphanumeric() == 'c8'
			) {
				$starting_square = new ChessSquare('a8');
				$ending_square = new ChessSquare('d8');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			}
		} elseif ( $this->color == ChessPiece::WHITE ) {
			if (
				$this->piece_type == ChessPiece::KING &&
				$this->starting_square->get_alphanumeric() == 'e1' &&
				$this->ending_square->get_alphanumeric() == 'g1'
			) {
				$starting_square = new ChessSquare('h1');
				$ending_square = new ChessSquare('f1');
				$this->board->make_additional_move_on_same_turn($starting_square, $ending_square);
				$this->castling = TRUE;
			} elseif (
				$this->piece_type == ChessPiece::KING &&
				$this->starting_square->get_alphanumeric() == 'e1' &&
				$this->ending_square->get_alphanumeric() == 'c1'
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
			$this->starting_square->get_alphanumeric() == 'e8' &&
			$this->ending_square->get_alphanumeric() == 'g8' &&
			$this->piece_type == ChessPiece::KING &&
			$this->color = ChessPiece::BLACK
		) {
			$string .= 'O-O';
		} elseif (
			$this->starting_square->get_alphanumeric() == 'e1' &&
			$this->ending_square->get_alphanumeric() == 'g1' &&
			$this->piece_type == ChessPiece::KING &&
			$this->color = ChessPiece::WHITE
		) {
			$string .= 'O-O';
		} elseif (
			$this->starting_square->get_alphanumeric() == 'e8' &&
			$this->ending_square->get_alphanumeric() == 'c8' &&
			$this->piece_type == ChessPiece::KING &&
			$this->color = ChessPiece::BLACK
		) {
			$string .= 'O-O-O';
		} elseif (
			$this->starting_square->get_alphanumeric() == 'e1' &&
			$this->ending_square->get_alphanumeric() == 'c1' &&
			$this->piece_type == ChessPiece::KING &&
			$this->color = ChessPiece::WHITE
		) {
			$string .= 'O-O-O';
		} else {
			// type of piece
			if ( $this->piece_type == ChessPiece::PAWN && $this->capture ) {
				$string .= substr($this->starting_square->get_alphanumeric(), 0, 1);
			} elseif ( $this->piece_type != ChessPiece::PAWN ) {
				$string .= strtoupper(self::PIECE_LETTERS[$this->piece_type]);
			}
			
			// disambiguation of rank/file/square
			$string .= $this->disambiguation;
			
			// capture?
			if ( $this->capture ) {
				$string .= 'x';
			}
			
			// destination square
			$string .= $this->ending_square->get_alphanumeric();
			
			// en passant
			if ( $this->en_passant ) {
				$string .= 'e.p.';
			}
			
			// pawn promotion
			if ( $this->promotion_piece_type == ChessPiece::QUEEN ) {
				$string .= '=Q';
			} elseif ( $this->promotion_piece_type == ChessPiece::ROOK ) {
				$string .= '=R';
			} elseif ( $this->promotion_piece_type == ChessPiece::BISHOP ) {
				$string .= '=B';
			} elseif ( $this->promotion_piece_type == ChessPiece::KNIGHT ) {
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
			$this->promotion_piece_type == ChessPiece::ROOK ||
			$this->promotion_piece_type == ChessPiece::BISHOP ||
			$this->promotion_piece_type == ChessPiece::KNIGHT
		) {
			return "";
		} else {		
			return $this->starting_square->get_alphanumeric() . $this->ending_square->get_alphanumeric();
		}
	}
}
