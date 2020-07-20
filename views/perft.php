<?php

if (! defined('VIEWER')) {
    die("This file needs to be included into a viewer.");
}

?>

<!DOCTYPE html>

<html lang="en-us">
	<head>
		<meta charset="utf-8">
		
		<title>
			Perft - PHP Chess - Red Dragon Web Design
		</title>
		
		<link rel="stylesheet" href="assets/style.css">
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	</head>

	<body>
		<h1>
		Perft
		</h1>
		
		<p>
		Perft is a way of testing chess engine accuracy and speed. It counts the number of legal moves for the current board, then the legal moves for all of those resulting boards, etc. until it gets to the depth desired. Then you compare the total number of legal moves (the perft) to the known correct number.
		</p>
		
		<form id="import_fen">
			<p>
				FEN:<br>
				<input type="text" name="fen" value="<?php echo $fen; ?>"><br>
			</p>
			
			<p>
				Depth:<br>
				<input type="number" name="perft_depth" value="<?php echo $perft_depth; ?>"><br>
			</p>
			
			<p>
				Detail Level:<br>
				<input type="radio" name="detail_level" value="1" <?php if ( $detail_level == 1 ) {echo "checked";} ?>> Nodes Only
				<input type="radio" name="detail_level" value="2" <?php if ( $detail_level == 2 ) {echo "checked";} ?>> Details
				<input type="radio" name="detail_level" value="3" <?php if ( $detail_level == 3 ) {echo "checked";} ?>> Checks & Checkmates
			</p>
			
			<p>
				Debug:<br>
				<input type="radio" name="debug" value="0" <?php if ( $debug == 0 ) {echo "checked";} ?>> Normal
				<input type="radio" name="debug" value="1" <?php if ( $debug == 1 ) {echo "checked";} ?>> Debug
			</p>
			
			<p>
				<input type="submit" value="Perft">
			</p>
		</form>
		
		<table>
			<thead>
				<tr>
						<th>Depth</th>
						<th>Nodes</th>
					<?php if ( $detail_level >= 2 ): ?>
					
						<th>Captures</th>
						<th>En Passants</th>
						<th>Castles</th>
						<th>Promotions</th>
						
					<?php if ( $detail_level >= 3 ): ?>
					
						<th>Checks</th>
						<th>Checkmates</th>
					<?php endif; ?>
						
					<?php endif; ?>
					
				</tr>
			</thead>
			
			<tbody>
				<?php foreach ( $data as $value ): ?>
					
					<tr>
							<td><?php echo $value['depth']; ?></td>
							<td><?php echo $value['nodes']; ?></td>
						<?php if ( $detail_level >= 2 ): ?>
						
							<td><?php echo $value['captures']; ?></td>
							<td><?php echo $value['en_passants']; ?></td>
							<td><?php echo $value['castles']; ?></td>
							<td><?php echo $value['promotions']; ?></td>
							
						<?php if ( $detail_level >= 3 ): ?>
							<td><?php echo $value['checks']; ?></td>
							<td><?php echo $value['checkmates']; ?></td>
						<?php endif; ?>
						
						<?php endif; ?>
						
					</tr>
				<?php endforeach; ?>
				
			</tbody>
		</table>
		
		<p>
			<?php
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish = $time;
			$total_time = round(($finish - $start), 4);
			
			$total_time *= 1000;
			$total_time = round($total_time);
			
			?>
			
			Total Load Time: <?php echo $total_time; ?> ms<br>
			Per Move Tree: <?php echo round($total_time / $move_trees_generated, 2); ?> ms<br>
			<?php if ( function_exists('xdebug_get_code_coverage') ): ?>
			
				<br>
				XDebug is loaded. Turn it off and your code will go 9x faster!<br>
			<?php endif; ?>
			
		</p>
		
		<?php if ( $debug ): ?>
		
			<p>
				<b><i><u>DEBUG</u></i></b><br>
				<?php $count = 0; ?>
				<?php foreach ( $debug_data as $key => $value ): ?>
				
					<?php $count++; ?>
					<?php echo $count; ?> - <?php echo $key; ?> - <?php echo $value['count']; ?> - <?php echo $value['fen']; ?><br>
				<?php endforeach; ?>
				
			</p>
		<?php endif; ?>
		
	</body>
</html>