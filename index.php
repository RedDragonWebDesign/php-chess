<?php

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('helpers/helper_functions.php');
require_once('models/ChessRulebook.php');
require_once('models/ChessBoard.php');
require_once('models/ChessPiece.php');
require_once('models/ChessMove.php');
require_once('models/ChessSquare.php');

$board = new ChessBoard();

if ( isset($_GET['reset']) ) {
	// Skip this conditional. ChessGame's FEN is the default, new game FEN and doesn't need to be set again.
} elseif ( isset($_GET['move']) ) {
	$board->import_fen($_GET['move']);
} elseif ( isset($_GET['fen']) ) {
	$board->import_fen($_GET['fen']);
}

$fen = $board->export_fen();
$side_to_move = $board->get_side_to_move_string();
$who_is_winning = $board->get_who_is_winning_string();
$graphical_board_array = $board->get_graphical_board();
$legal_moves = ChessRulebook::get_legal_moves_list($board->color_to_move, $board);

define('VIEWER', true);
require_once('views/index.php');
