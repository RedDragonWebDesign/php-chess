<?php

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once('helpers/helper_functions.php');
require_once('models/ChessRulebook.php');
require_once('models/ChessBoard.php');
require_once('models/ChessPiece.php');
require_once('models/ChessMove.php');
require_once('models/ChessSquare.php');

$board = new ChessBoard();

if ( isset($_GET['fen']) ) {
	$board->import_fen($_GET['fen']);
}

$fen = $board->export_fen();

const PERFT_DEPTH = 2;
const COUNT_CHECKS_AND_CHECKMATES = FALSE;
$data = array();

$legal_moves[0][0] = new ChessMove(NULL, NULL, NULL, NULL, NULL, $board);
$move_trees_generated = 0;

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

for ( $depth = 1; $depth <= PERFT_DEPTH; $depth++ ) {
	$data[$depth]['depth'] = $depth;
	$data[$depth]['nodes'] = 0;
	$data[$depth]['captures'] = 0;
	$data[$depth]['en_passants'] = 0;
	$data[$depth]['castles'] = 0;
	$data[$depth]['promotions'] = 0;
	if ( COUNT_CHECKS_AND_CHECKMATES ) {
		$data[$depth]['checks'] = 0;
		$data[$depth]['checkmates'] = 0;
	} else {
		$data[$depth]['checks'] = '';
		$data[$depth]['checkmates'] = '';
	}
	
	$legal_moves[$depth] = array();
	foreach ( $legal_moves[$depth - 1] as $key => $move ) {
		$legal_moves_list = ChessRulebook::get_legal_moves_list($move->board->color_to_move, $move->board, TRUE, TRUE, COUNT_CHECKS_AND_CHECKMATES);
		$move_trees_generated++;
		foreach ( $legal_moves_list as $key2 => $move2 ) {
			array_push($legal_moves[$depth], $move2);
			
			$data[$depth]['nodes']++;
			
			if ( $move2->capture ) {
				$data[$depth]['captures']++;
			}
			
			if ( $move2->en_passant ) {
				$data[$depth]['en_passants']++;
			}
			
			if ( $move2->castling ) {
				$data[$depth]['castles']++;
			}
			
			if ( $move2->promotion_piece_type ) {
				$data[$depth]['promotions']++;
			}
			
			if ( COUNT_CHECKS_AND_CHECKMATES ) {
				if ( $move2->check ) {
					$data[$depth]['checks']++;
				}
				
				if ( $move2->checkmate ) {
					$data[$depth]['checkmates']++;
				}
			}
		}
	}
	unset($legal_moves[$depth - 1]);
}

require_once('views/perft.html');
