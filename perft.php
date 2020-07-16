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
$perft_depth = isset($_GET['perft_depth']) ? $_GET['perft_depth'] : 2;
$detail_level = isset($_GET['detail_level']) ? $_GET['detail_level'] : 1;
$debug = isset($_GET['debug']) ? $_GET['debug'] : 0;
$data = array();
$data['move_trees_generated'] = 0; // Used to get the average move time per tree.

function perft($current_depth, $max_depth, $color_to_move, $board, $data, $detail_level) {
	$data['move_trees_generated']++;
	$legal_moves_list = ChessRulebook::get_legal_moves_list($color_to_move, $board, TRUE, TRUE, $detail_level >= 3);
	
	if ( ! isset($data[$current_depth]['nodes']) ) {
		$data[$current_depth]['depth'] = $current_depth;
		$data[$current_depth]['nodes'] = 0;
		$data[$current_depth]['captures'] = 0;
		$data[$current_depth]['en_passants'] = 0;
		$data[$current_depth]['castles'] = 0;
		$data[$current_depth]['promotions'] = 0;
		$data[$current_depth]['checks'] = 0;
		$data[$current_depth]['checkmates'] = 0;
	}
	
	$data[$current_depth]['nodes'] += count($legal_moves_list);
	
	if ( $detail_level >= 2 ) {
		foreach ( $legal_moves_list as $move ) {
			$data = get_details($move, $current_depth, $data, $detail_level);
		}
	}
	
	if ( $current_depth != $max_depth ) {
		foreach ( $legal_moves_list as $move ) {
			// Doing this recursively instead of a list of moves for each depth prevents "out of memory" errors.
			$data = perft($current_depth + 1, $max_depth, $move->board->color_to_move, $move->board, $data, $detail_level);
		}
	}
	
	return $data;
}

function get_details($move, $depth, $data, $detail_level) {
	if ( $move->capture ) {
		$data[$depth]['captures']++;
	}
	
	if ( $move->en_passant ) {
		$data[$depth]['en_passants']++;
	}
	
	if ( $move->castling ) {
		$data[$depth]['castles']++;
	}
	
	if ( $move->promotion_piece_type ) {
		$data[$depth]['promotions']++;
	}
	
	if ( $detail_level >= 3 ) {
		if ( $move->check ) {
			$data[$depth]['checks']++;
		}
		
		if ( $move->checkmate ) {
			$data[$depth]['checkmates']++;
		}
	}
	
	return $data;
}

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

$data = perft(1, $perft_depth, $board->color_to_move, $board, $data, $detail_level);

$move_trees_generated = $data['move_trees_generated'];
unset($data['move_trees_generated']);

if ( $debug ) {
	$legal_moves_list1 = ChessRulebook::get_legal_moves_list($board->color_to_move, $board, TRUE, TRUE, FALSE);
	
	foreach ( $legal_moves_list1 as $move1 ) {
		$key1 = $move1->starting_square->get_alphanumeric() . '-' . $move1->ending_square->get_alphanumeric();
		
		$debug_data[$key1] = array(
			'count' => 0,
			'fen' => $move1->board->export_fen(),
		);
		
		$legal_moves_list2 = ChessRulebook::get_legal_moves_list($move1->board->color_to_move, $move1->board, TRUE, TRUE, FALSE);
		
		foreach ( $legal_moves_list2 as $move2 ) {
			$key2 = $move2->starting_square->get_alphanumeric() . '-' . $move2->ending_square->get_alphanumeric();
			
			$debug_data[$key1]['count']++;
		}
	}
}
	
if ( $debug ) {
	ksort($debug_data);
}

define('VIEWER', true);
require_once('views/perft.php');
