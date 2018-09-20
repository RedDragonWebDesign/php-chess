<?php

class ChessBoard {
	const PIECE_LETTERS = array(
		'p' => ChessPiece::PAWN,
		'n' => ChessPiece::KNIGHT,
		'b' => ChessPiece::BISHOP,
		'r' => ChessPiece::ROOK,
		'q' => ChessPiece::QUEEN,
		'k' => ChessPiece::KING
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
	const FEN_REGEX_FORMAT = '/^([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8})\/([rnbqkpRNBQKP12345678]{1,8}) ([bw]{1}) ([-KQkq]{1,4}) ([a-h1-8-]{1,2})( (\d{1,2}) (\d{1,4}))?$/';
	const DEFAULT_FEN = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
	
	public $board = array(); // $board[y][x], or in this case, $board[rank][file]
	public $color_to_move;
	public $castling = array(); // format is array('white_can_castle_kingside' => TRUE, etc.)
	public $en_passant_target_square = NULL;
	public $halfmove_clock;
	public $fullmove_number;
	
	function __construct($fen = self::DEFAULT_FEN) {
		$this->import_fen($fen);
	}
	
	function __clone() {
		if ( $this->board ) {
			for ( $rank = 1; $rank <= 8; $rank++ ) {
				for ( $file = 1; $file <= 8; $file++ ) {
					// if there's a piece there
					if ( $this->board[$rank][$file] ) {
						// clone the piece so it's not pointing at an old piece
						$this->board[$rank][$file] = clone $this->board[$rank][$file];
					}
				}
			}
		}
	}
	
	function import_fen($fen) {
		// TODO: FEN probably needs its own class.
		// Then it can have a method for each section of code below.
		
		$fen = trim($fen);
		
		// set everything back to default
		$legal_moves = array();
		$checkmate = FALSE;
		$stalemate = FALSE;
		$move_list = array();
		// TODO: add more
		
		// Basic format check. This won't catch everything, but it will catch a lot of stuff.
		// This also parses the info we need into $matches[1] through $matches[14]
		// $matches[12] is skipped.
		// TODO: Make this stricter so that it catches everything.
		$valid_fen = preg_match(self::FEN_REGEX_FORMAT, $fen, $matches);
		
		if ( ! $valid_fen ) {
			throw new Exception('Invalid FEN');
		}
		
		// ******* CREATE PIECES AND ASSIGN THEM TO SQUARES *******
		
		// Set all board squares to NULL. That way we don't have to blank them in the loop below. We can just overwrite the NULL with a piece.
		for ( $i = 1; $i <= 8; $i++ ) {
			for ( $j = 1; $j <= 8; $j++ ) {
				$this->board[$i][$j] = NULL;
			}
		}
		
		// Create $rank variables with strings that look like this
			// rnbqkbnr
			// pppppppp
			// 8
			// PPPPPPPP
			// RNBQKBNR
			// 2p5
		// The numbers are the # of blank squares from left to right
		$rank = array();
		for ( $i = 1; $i <= 8; $i++ ) {
			// Match string = 1, but rank = 8. Fix it here to avoid headaches.
			$rank = $this->invert_rank_or_file_number($i);
			$rank_string[$rank] = $matches[$i];
		}
		
		// Process $rank variable strings, convert to pieces and add them to $this->board[][]
		foreach ( $rank_string as $rank => $string ) {
			$file = 1;
			
			for ( $i = 1; $i <= strlen($string); $i++ ) {
				$char = substr($string, $i - 1, 1);
				
				// Don't use is_int here. $char is a string. Use is_numeric instead.
				if ( is_numeric($char) ) {
					$file = $file + $char;
				} else {
					$square = $this->number_to_file($file) . $rank;
					
					if ( ctype_upper($char) ) {
						$color = ChessPiece::WHITE;
					} else {
						$color = ChessPiece::BLACK;
					}
					
					$type = self::PIECE_LETTERS[strtolower($char)];
					
					$this->board[$rank][$file] = new ChessPiece($color, $square, $type);
					
					$file++;
				}
			}
		}
		
		// ******* SET COLOR TO MOVE *******
		if ( $matches[9] == 'w' ) {
			$this->color_to_move = ChessPiece::WHITE;
		} elseif ( $matches[9] == 'b' ) {
			$this->color_to_move = ChessPiece::BLACK;
		} else {
			throw new Exception('Invalid FEN - Invalid Color To Move');
		}
		
		// Set all castling to false. Only set to true if letter is present in FEN. Prevents bugs.
		$this->castling['white_can_castle_kingside'] = FALSE;
		$this->castling['white_can_castle_queenside'] = FALSE;
		$this->castling['black_can_castle_kingside'] = FALSE;
		$this->castling['black_can_castle_queenside'] = FALSE;
		
		// ******* SET CASTLING POSSIBILITIES *******
		// strpos is case sensitive, so that's good
		if ( strpos($matches[10], 'K') !== FALSE ) {
			$this->castling['white_can_castle_kingside'] = TRUE;
		}

		if ( strpos($matches[10], 'Q') !== FALSE ) {
			$this->castling['white_can_castle_queenside'] = TRUE;
		}

		if ( strpos($matches[10], 'k') !== FALSE ) {
			$this->castling['black_can_castle_kingside'] = TRUE;
		}

		if ( strpos($matches[10], 'q') !== FALSE ) {
			$this->castling['black_can_castle_queenside'] = TRUE;
		}
		
		// ******* SET EN PASSANT TARGET SQUARE *******
		if ( $matches[11] == '-' ) {
			$this->en_passant_target_square = FALSE;
		} else {
			$this->en_passant_target_square = new ChessSquare($matches[11]);
		}
		// ChessPiece throws its own exceptions, so no need to throw one here.
		
		// Normal (long) FEN
		if ( isset($matches[12]) ) {
			// ******* SET HALFMOVE CLOCK *******
			$this->halfmove_clock = $matches[13];
			
			// ******* SET FULLMOVE NUMBER *******
			$this->fullmove_number = $matches[14];
			
			// ******* SET HALFMOVE NUMBER *******
			$this->halfmove_number = $matches[14] * 2 - 1;
			if ( $this->color_to_move == ChessPiece::BLACK ) {
				$this->halfmove_number++;
			}
		// Short fen. Use default values.
		} else {

			$this->halfmove_clock = 0;
			$this->fullmove_number = 1;
		}
	}
	
	function export_fen() {
		$string = '';
		
		// A chessboard looks like this
			// a8 b8 c8 d8
			// a7 b7 c7 d7
			// etc.
		// But we want to print them starting with row 8 first.
		// So we need to adjust the loops a bit.
		
		for ( $rank = 8; $rank >= 1; $rank-- ) {
			$empty_squares = 0;
			
			for ( $file = 1; $file <= 8; $file++ ) {
				$piece = $this->board[$rank][$file];
				
				if ( ! $piece ) {
					$empty_squares++;
				} else {
					if ( $empty_squares ) {
						$string .= $empty_squares;
						$empty_squares = 0;
					}
					$string .= $piece->get_fen_symbol();
				}
			}
			
			if ( $empty_squares ) {
				$string .= $empty_squares;
			}
			
			if ( $rank != 1 ) {
				$string .= "/";
			}
		}
		
		if ( $this->color_to_move == ChessPiece::WHITE ) {
			$string .= " w ";
		} elseif ( $this->color_to_move == ChessPiece::BLACK ) {
			$string .= " b ";
		}
		
		if ( $this->castling['white_can_castle_kingside'] ) {
			$string .= "K";
		}
		
		if ( $this->castling['white_can_castle_queenside'] ) {
			$string .= "Q";
		}
		
		if ( $this->castling['black_can_castle_kingside'] ) {
			$string .= "k";
		}
		
		if ( $this->castling['black_can_castle_queenside'] ) {
			$string .= "q";
		}
		
		if (
			! $this->castling['white_can_castle_kingside'] &&
			! $this->castling['white_can_castle_queenside'] &&
			! $this->castling['black_can_castle_kingside'] &&
			! $this->castling['black_can_castle_queenside']
		) {
			$string .= "-";
		}
		
		if ( $this->en_passant_target_square ) {
			$string .= " " . $this->en_passant_target_square->get_alphanumeric();
		} else {
			$string .= " -";
		}
		
		$string .= " " . $this->halfmove_clock . ' ' . $this->fullmove_number;
		
		return $string;
	}
	
	// Keeping this for debug reasons.
    function get_ascii_board() {
        $string = '';

        if ( $this->color_to_move == ChessPiece::WHITE ) {
            $string .= "White To Move";
        } elseif ( $this->color_to_move == ChessPiece::BLACK ) {
            $string .= "Black To Move";
        }
		
        // A chessboard looks like this
            // a8 b8 c8 d8
            // a7 b7 c7 d7
            // etc.
        // But we want to print them starting with row 8 first.
        // So we need to adjust the loops a bit.

        for ( $rank = 8; $rank >= 1; $rank-- ) {
            $string .= "<br />";

            for ( $file = 1; $file <= 8; $file++ ) {
                $square = $this->board[$rank][$file];

                if ( ! $square ) {
                    $string .= "*";
                } else {
                    $string .= $this->board[$rank][$file]->get_unicode_symbol();
                }
            }
        }
		$string .= "<br /><br />";

        return $string;
    }
	
	function get_graphical_board() {
		// We need to throw some variables into an array so our view can build the board.
		// The array shall be in the following format:
			// square_color = black / white
			// id = a1-h8
			// piece = HTML unicode for that piece
		
		// A chessboard looks like this
			// a8 b8 c8 d8
			// a7 b7 c7 d7
			// etc.
		// But we want to print them starting with row 8 first.
		// So we need to adjust the loops a bit.
		
		$graphical_board_array = array();
		for ( $rank = 8; $rank >= 1; $rank-- ) {
			for ( $file = 1; $file <= 8; $file++ ) {
				$piece = $this->board[$rank][$file];
				
				// SQUARE COLOR
				if ( ($rank + $file) % 2 == 1 ) {
					$graphical_board_array[$rank][$file]['square_color'] = 'white';
				} else {
					$graphical_board_array[$rank][$file]['square_color'] = 'black';
				}
				
				// ID
				$graphical_board_array[$rank][$file]['id'] = self::FILE_NUMS_AND_LETTERS[$file] . $rank;
				
				// PIECE
				if ( ! $piece ) {
					$graphical_board_array[$rank][$file]['piece'] = '';
				} else {
					$graphical_board_array[$rank][$file]['piece'] = $this->board[$rank][$file]->get_unicode_symbol();
				}
			}
		}
		
		return $graphical_board_array;
	}
	
	function get_side_to_move_string() {
		$string = '';
		
		if ( $this->color_to_move == ChessPiece::WHITE ) {
			$string .= "White To Move";
		} elseif ( $this->color_to_move == ChessPiece::BLACK ) {
			$string .= "Black To Move";
		}
		
		return $string;
	}
	
	function get_who_is_winning_string() {
		$points = 0;
		
		foreach ( $this->board as $key1 => $value1 ) {
			foreach ( $value1 as $key2 => $piece ) {
				if ( $piece ) {
					$points += $piece->value;
				}
			}
		}
		
		if ( $points > 0 ) {
			return "Material: White Ahead By $points";
		} elseif ( $points < 0 ) {
			$points *= -1;
			return "Material: Black Ahead By $points";
		} else {
			return "Material: Equal";
		}
	}
	
	function invert_rank_or_file_number($number) {
		// 1 => 8
		// 2 => 7
		// etc.
		
		return 9 - $number;
	}
	
	function number_to_file($number) {
		return self::FILE_NUMS_AND_LETTERS[$number];
	}
	
	// Note: This does not check for and reject illegal moves. It is up to code in the ChessGame class to generate a list of legal moves, then only make_move those moves.
	// In fact, sometimes make_move will be used on illegal moves (king in check moves), then the illegal moves will be deleted from the list of legal moves in a later step.
	function make_move($old_square, $new_square) {
		$moving_piece = clone $this->board[$old_square->rank][$old_square->file];
		
		$this->en_passant_target_square = NULL;
		
		$is_capture = $this->board[$new_square->rank][$new_square->file];
		
		if ( $moving_piece->type == ChessPiece::PAWN || $is_capture ) {
			$this->halfmove_clock = 0;
		} else {
			$this->halfmove_clock++;
		}
		
		$this->board[$new_square->rank][$new_square->file] = $moving_piece;
		
		// Update $moving_piece->square too to avoid errors.
		$moving_piece->square = $new_square;
		
		$this->board[$old_square->rank][$old_square->file] = NULL;
		
		if ( $this->color_to_move == ChessPiece::BLACK ) {
			$this->fullmove_number++;
		}
		
		$this->flip_color_to_move();
	}
	
	// Used to move the rook during castling.
	// Can't use make_move because it messes up color_to_move, halfmove, and fullmove.
	function make_additional_move_on_same_turn($old_square, $new_square) {
		$moving_piece = clone $this->board[$old_square->rank][$old_square->file];
		
		$this->board[$new_square->rank][$new_square->file] = $moving_piece;
		
		// Update $moving_piece->square too to avoid errors.
		$moving_piece->square = $new_square;
		
		$this->board[$old_square->rank][$old_square->file] = NULL;
	}
	
	function flip_color_to_move() {
		if ( $this->color_to_move == ChessPiece::WHITE ) {
			$this->color_to_move = ChessPiece::BLACK;
		} elseif ( $this->color_to_move == ChessPiece::BLACK ) {
			$this->color_to_move = ChessPiece::WHITE;
		}
	}
	
	function square_is_occupied($square) {
		$rank = $square->rank;
		$file = $square->file;
		
		if ( $this->board[$rank][$file] ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function get_king_square($color) {
		foreach ( $this->board as $key => $value ) {
			foreach ( $value as $key2 => $piece ) {
				if ( $piece ) {
					if ( $piece->type == ChessPiece::KING && $piece->color == $color ) {
						return $piece->square;
					}
				}
			}
		}
		
		return NULL;
	}
	
	function remove_piece_from_square($square) {
		$rank = $square->rank;
		$file = $square->file;
	
		$this->board[$rank][$file] = NULL;
	}
	
	function count_pieces_on_rank($type, $rank, $color) {
		$count = 0;
		
		for ( $i = 1; $i <= 8; $i++ ) {
			$piece = $this->board[$rank][$i];
			
			if ( $piece ) {
				if ( $piece->type == $type && $piece->color == $color ) {
					$count++;
				}
			}
		}
		
		return $count;
	}
	
	function count_pieces_on_file($type, $file, $color) {
		$count = 0;
		
		for ( $i = 1; $i <= 8; $i++ ) {
			$piece = $this->board[$i][$file];
			
			if ( $piece ) {
				if ( $piece->type == $type && $piece->color == $color ) {
					$count++;
				}
			}
		}
		
		return $count;
	}
}
