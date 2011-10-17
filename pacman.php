<?php
/**
 * A Pacman game.
 *
 * @author Johan Hasselqvist
 */
?>
<!DOCTYPE html>
<html>
<head>
	<title>Pacman</title>
	<meta charset="utf-8" />
	
	<style>
		body {
			width: 406px;
			margin: auto;
			margin-top: 10px;
		}
		svg {
			background-color: black;
			border: 3px solid blue;
			border-radius: 9px;
		}
		#player {
			fill: yellow;
		}
		rect {
			stroke: blue;
			stroke-width: 3;
		}
		#ghost_box {
			stroke: blue;
			stroke-width: 3;
		}
		.ball {
			fill: white;
		}
		#ball_20_20, #ball_20_380, #ball_380_20, #ball_380_380 {
			stroke-width: 5;
			stroke: white;
		}
		#win_or_lose_text {
			color: white;
			position: absolute;
			top: 70px;
			width: 400px;
			font-size: 80px;
			text-align: center;
		}
		.ghost {
			fill: red;
			stroke: none;
		}
		.ghost_eye {
			stroke: white;
			stroke-width: 2;
		}
	</style>
</head>
<body>
	<svg id="game_screen"
		 version="1.1"
		 baseProfile="full"
		 width="400"
		 height="400"
		 viewport="0 0 400 400">
				
		<?php
			for ($y = 20; $y < 400; $y = $y + 40) {
				echo "\t\t<circle id='ball_20_{$y}' class='ball' cx='20' cy='{$y}' r='4' />\n";
				for ($x = 60; $x <= 400; $x = $x + 40) {
					if (!($x >= 120 && $x <= 260 && $y >= 120 && $y <= 240)) {
						echo "\t\t<circle id='ball_{$x}_{$y}' class='ball' cx='{$x}' cy='{$y}' r='4' />\n";
					}
				}
			}
		?>
		<g>
		<circle id="player" cx="200" cy="100" r="15" />
		<polygon id="mouth" points="200,100 185,93 185,107" />
		</g>
		
		<rect id="block1" x="40" y="40" height="40" width="120" />
		<rect id="block2" x="240" y="40" height="40" width="120" />
		<rect id="block3" x="-3" y="120" height="40" width="83" />
		<rect id="block4" x="320" y="120" height="40" width="83" />
			<!-- The following five lines are not visible, they are only there for collision detection -->
			<rect id="block10" x="120" y="220" height="20" width="60" />
			<rect id="block11" x="120" y="120" height="100" width="20" />
			<rect id="block12" x="140" y="120" height="20" width="120" />
			<rect id="block13" x="260" y="120" height="100" width="20" />
			<rect id="block14" x="220" y="220" height="20" width="60" />
			<!-- End of invisible blocks -->
		<polygon class="box" id="ghost_box" points="140,220 180,220 180,240 120,240 120,120 280,120 280,240 220,240 220,220 260,220 260,140 140,140" />
		<rect id="block5" x="40" y="200" height="40" width="40" />
		<rect id="block6" x="320" y="200" height="40" width="40" />
		<rect id="block7" x="40" y="280" height="40" width="80" />
		<rect id="block8" x="160" y="280" height="40" width="80" />
		<rect id="block9" x="280" Y="280" height="40" width="80" />
		<rect id="block0" x="40" y="360" height="43" width="320" />
		
		<?php
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "\t\t<circle id='ghost{$ghost_nr}_top'  class='ghost'	   cx='200'	cy='180' r='12' />\n";
				echo "\t\t<rect	  id='ghost{$ghost_nr}_bot'  class='ghost'	    x='188'	 y='180' height='16' width='24' />\n";
				echo "\t\t<circle id='ghost{$ghost_nr}_eye1' class='ghost_eye' cx='195'	cy='180' r='2' />\n";
				echo "\t\t<circle id='ghost{$ghost_nr}_eye2' class='ghost_eye' cx='205'	cy='180' r='2' />\n";
			}
		?>
	</svg>
		<p id="win_or_lose_text"></p>
	
	<script>
		var game_screen = document.getElementById("game_screen");
		var player = document.getElementById("player");
		var mouth = document.getElementById("mouth");
		var player_x_speed = 0;
		var player_y_speed = 0;
		var player_x_pos;
		var player_y_pos;
		var total_balls_eaten = 0;
		var has_not_won = true;
		var has_not_lost = true;
		
		<?php
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "var ghost{$ghost_nr}_x_min;\n";
				echo "var ghost{$ghost_nr}_x_max;\n";
				echo "var ghost{$ghost_nr}_y_min;\n";
				echo "var ghost{$ghost_nr}_y_max;\n";
			}
			echo "\n";
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "var ghost{$ghost_nr}_y_pos;\n";
			}
			echo "\n";
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "var ghost{$ghost_nr} = document.getElementById('ghost{$ghost_nr}_top');\n";
			}
			echo "\n";
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "var ghost{$ghost_nr}_y_speed = -1;\n";
			}
			echo "\n";
			for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
				echo "var ghost{$ghost_nr}_x_speed = 0;\n";
			}
		?>
		
		var block_y_max = new Array();
		var block_y_min = new Array();
		var block_x_max = new Array();
		var block_x_min = new Array();
		var number_of_blocks = 14;
		
		for(block_nr = 0; block_nr <= number_of_blocks; block_nr++) {
			block_x_min[block_nr] = parseFloat(document.getElementById("block" + block_nr).getAttribute("x")) - 15;
			block_x_max[block_nr] = parseFloat(document.getElementById("block" + block_nr).getAttribute("x")) + parseFloat(document.getElementById("block" + block_nr).getAttribute("width")) + 15;
			block_y_min[block_nr] = parseFloat(document.getElementById("block" + block_nr).getAttribute("y")) - 15;
			block_y_max[block_nr] = parseFloat(document.getElementById("block" + block_nr).getAttribute("y")) + parseFloat(document.getElementById("block" + block_nr).getAttribute("height")) + 15;
		}
		
		function main(){
		
			if (total_balls_eaten >= 62) {
				win();
			}
			<?php
				echo "else if(player_x_pos <= ghost1_x_max && player_x_pos >= ghost1_x_min && player_y_pos <= ghost1_y_max && player_y_pos >= ghost1_y_min) {";
				echo "lose();";
				echo "}";
			?>
			else {
			player_x_pos = parseFloat(player.getAttribute("cx"));
			player_y_pos = parseFloat(player.getAttribute("cy"));
			
			<?php
				for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
					echo "ghost{$ghost_nr}_x_min = parseFloat(ghost{$ghost_nr}.getAttribute('cx')) - 27;\n";
					echo "ghost{$ghost_nr}_x_max = parseFloat(ghost{$ghost_nr}.getAttribute('cx')) + 27;\n";
					echo "ghost{$ghost_nr}_y_min = parseFloat(ghost{$ghost_nr}.getAttribute('cy')) - 27;\n";
					echo "ghost{$ghost_nr}_y_max = parseFloat(ghost{$ghost_nr}.getAttribute('cy')) + parseFloat(document.getElementById('ghost{$ghost_nr}_bot').getAttribute('height')) + 15;\n";
				}
				for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
					echo "ghost{$ghost_nr}_x_pos = parseFloat(ghost{$ghost_nr}.getAttribute('cx'));\n";
					echo "ghost{$ghost_nr}_y_pos = parseFloat(ghost{$ghost_nr}.getAttribute('cy'));\n";
				}
			?>
			
			for (y = 20; y <= 400; y = y + 40) {
				for (x = 60; x <= 400; x = x + 40) {
					if (player_x_pos <= x + 10 && player_x_pos >= x - 10 && player_y_pos <= y + 10 && player_y_pos >= y - 10 && document.getElementById("ball_" + x + "_" + y) != null ) {
						var ball_to_eat = document.getElementById("ball_" + x + "_" + y);
						ball_to_eat.parentNode.removeChild(ball_to_eat);
						total_balls_eaten ++;
						
					}
				}
				if (player_x_pos <= 30 && player_x_pos >= 10 && player_y_pos <= y + 10 && player_y_pos >= y - 10 && document.getElementById("ball_20_" + y) != null ) {
					var ball_to_eat = document.getElementById("ball_20_" + y);
					ball_to_eat.parentNode.removeChild(ball_to_eat);
					total_balls_eaten ++;
				}
			}
			
			if ((player_x_pos <= 15 && player_x_speed == 1) || (player_x_pos >= 385 && player_x_speed == -1)) {
				player_x_pos = player_x_pos;
				player_x_speed = 0;
			}
		
			<?php
				for($block_nr = 0; $block_nr <= 14; $block_nr++) {
					echo "
						else if (player_x_speed == 1 && block_x_min[{$block_nr}] <= player_x_pos && block_x_max[{$block_nr}] >= player_x_pos && block_y_max[{$block_nr}] >= player_y_pos && block_y_min[{$block_nr}] <= player_y_pos) {
							player_x_pos = player_x_pos + 2;
							player_x_speed = 0;
						}
						else if (player_x_speed == -1 && block_x_min[{$block_nr}] <= player_x_pos && block_x_max[{$block_nr}] >= player_x_pos && block_y_max[{$block_nr}] >= player_y_pos && block_y_min[{$block_nr}] <= player_y_pos) {
							player_x_pos = player_x_pos - 2;
							player_x_speed = 0;
						}
					";
				}
			?>
			else {
				player_x_pos = player_x_pos - player_x_speed;
				
				if(player_x_speed == 1) {
					mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos - 15) + "," + (player_y_pos - 7) + " " + (player_x_pos - 15) + "," + (player_y_pos + 7));
				}
				else if(player_x_speed == -1 ) {
					mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 15) + "," + (player_y_pos - 7) + " " + (player_x_pos + 15) + "," + (player_y_pos + 7));
				}
			}
			
			if ((player_y_pos <= 15 && player_y_speed == 1) || (player_y_pos >= 385 && player_y_speed == -1)) {
				player_y_pos = player_y_pos;
				player_y_speed = 0;
			}

			<?php
				for($block_nr = 0; $block_nr <= 14; $block_nr++) {
					echo"
						else if (player_y_speed == 1 && block_x_min[{$block_nr}] <= player_x_pos && block_x_max[{$block_nr}] >= player_x_pos && block_y_max[{$block_nr}] >= player_y_pos && block_y_min[{$block_nr}] <= player_y_pos) {
							player_y_pos = player_y_pos + 2;
							player_y_speed = 0;
						}
						else if (player_y_speed == -1 && block_x_min[{$block_nr}] <= player_x_pos && block_x_max[{$block_nr}] >= player_x_pos && block_y_max[{$block_nr}] >= player_y_pos && block_y_min[{$block_nr}] <= player_y_pos) {
							player_y_pos = player_y_pos - 2;
							player_y_speed = 0;
						}
					";
				}
			?>
			
			else {
				player_y_pos = player_y_pos - player_y_speed;
				
				if(player_y_speed == 1) {
					mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos - 15) + " " + (player_x_pos - 7) + "," + (player_y_pos - 15));
				}
				else if(player_y_speed == -1) {
					mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos + 15) + " " + (player_x_pos -7) + "," + (player_y_pos + 15));
				}
			}
			player.setAttribute("cx", player_x_pos);
			player.setAttribute("cy", player_y_pos);
			
			<?php
				for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
					echo "if (false) {\n";
					echo "	ghost{$ghost_nr}_y_speed = 0;\n";
					echo "	ghost{$ghost_nr}_x_speed = 0.5;\n";
					echo "}\n";
					echo "else if (true) {\n";
					echo "	ghost{$ghost_nr}_y_speed = 0.5;\n";
					echo "	ghost{$ghost_nr}_x_speed = 0;\n";
					echo "}\n";
					echo "else if (false) {\n";
					echo "	ghost{$ghost_nr}_y_speed = 0;\n";
					echo "	ghost{$ghost_nr}_x_speed = -0.5;\n";
					echo "}\n";
					echo "else if (false) {\n";
					echo "	ghost{$ghost_nr}_y_speed = -0.5;\n";
					echo "	ghost{$ghost_nr}_x_speed = 0;\n";
					echo "}\n";
				}
			?>
			<?php
				for ($ghost_nr = 1; $ghost_nr < 4; $ghost_nr ++) {
					echo "ghost{$ghost_nr}.setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost{$ghost_nr}_y_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_bot').setAttribute('y', ghost{$ghost_nr}_y_pos - ghost{$ghost_nr}_y_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost{$ghost_nr}_y_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost{$ghost_nr}_y_speed);\n";
					echo "\n";
					echo "ghost{$ghost_nr}.setAttribute('cx', ghost{$ghost_nr}_x_pos - ghost{$ghost_nr}_x_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_bot').setAttribute('x', ghost{$ghost_nr}_x_pos - 12 - ghost{$ghost_nr}_x_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cx', ghost{$ghost_nr}_x_pos - 5 - ghost{$ghost_nr}_x_speed);\n";
					echo "document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cx', ghost{$ghost_nr}_x_pos + 5 - ghost{$ghost_nr}_x_speed);\n";
				}
			?>
			
			setTimeout(main, 10);
			}
		}
		
		document.onkeydown = function(event) {
		
			var e = event.keyCode;
			
			if (e==37 /* left */) {
				player_x_speed = 1;
				player_y_speed = 0;
			}
			else if (e==38 /* up */) {
				player_y_speed = 1;
				player_x_speed = 0;
			}
			else if (e==39 /* right */) {
				player_x_speed = -1;
				player_y_speed = 0;
			}
			else if (e==40 /* down */) {
				player_y_speed = -1;
				player_x_speed = 0;
			}
		}
		function win() {
			if (has_not_won != false) {
				document.getElementById("win_or_lose_text").innerHTML = "You Win!";
				has_not_won = false;
			}
		}
		function lose() {
			if (has_not_lost != false) {
				document.getElementById("win_or_lose_text").innerHTML = "You Lost!";
				has_not_lost = false;
			}
		}
		
		window.onload = main();
	</script>
</body>
</html>