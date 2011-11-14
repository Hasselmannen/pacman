﻿<?php
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
		circle {
			fill: none;
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
			// Generates the white balls
			for ($y = 20; $y < 400; $y = $y + 40) {
				echo "\t\t<circle id='ball_20_{$y}' class='ball' cx='20' cy='{$y}' r='4' />\n";
				for ($x = 60; $x <= 400; $x = $x + 40) {
					if (!($x >= 120 && $x <= 260 && $y >= 120 && $y <= 240)) {
						echo "\t\t<circle id='ball_{$x}_{$y}' class='ball' cx='{$x}' cy='{$y}' r='4' />\n";
					}
				}
			}
		?>
		
		<!-- Start of the player -->
		<g>
		<circle id="player" cx="220" cy="100" r="15" />
		<polygon id="mouth" points="220,100 205,93 205,107" />
		</g>
		<!-- End of player -->
		
		<!-- Start of waypoints where it is possible to turn -->
		<circle id="waypoint1" cx="20"  cy="20" r="5" />
		<circle id="waypoint2" cx="220" cy="20" r="5" />
		<circle id="waypoint3" cx="380" cy="20" r="5" />
		
		<circle id="waypoint4" cx="20"  cy="100" r="5" />
		<circle id="waypoint5" cx="100" cy="100" r="5" />
		<circle id="waypoint6" cx="220" cy="100" r="5" />
		<circle id="waypoint7" cx="300" cy="100" r="5" />
		<circle id="waypoint8" cx="380" cy="100" r="5" />
		
		<circle id="waypoint9" cx="20"  cy="180" r="5" />
		<circle id="waypoint10" cx="100" cy="180" r="5" />
		<circle id="waypoint11" cx="300" cy="180" r="5" />
		<circle id="waypoint12" cx="380" cy="180" r="5" />
		
		<circle id="waypoint13" cx="20" cy="260" r="5" />
		<circle id="waypoint14" cx="100" cy="260" r="5" />
		<circle id="waypoint15" cx="140" cy="260" r="5" />
		<circle id="waypoint16" cx="260" cy="260" r="5" />
		<circle id="waypoint17" cx="300" cy="260" r="5" />
		<circle id="waypoint18" cx="380" cy="260" r="5" />
		
		<circle id="waypoint19" cx="20" cy="340" r="5" />
		<circle id="waypoint20" cx="140" cy="340" r="5" />
		<circle id="waypoint21" cx="260" cy="340" r="5" />
		<circle id="waypoint22" cx="380" cy="340" r="5" />
		
		<circle id="waypoint23" cx="20" cy="380" r="5" />
		<circle id="waypoint24" cx="380" cy="380" r="5" />
		
		<circle id="ghost_waypoint" cx="200" cy="260" r="5" />
		<!-- End of waypoints -->
		
		<!-- Start of the blocks on the map -->
		<rect x="40" y="40" height="40" width="160" />
		<rect x="240" y="40" height="40" width="120" />
		<rect x="-3" y="120" height="40" width="83" />
		<rect x="320" y="120" height="40" width="83" />
		<polygon id="ghost_box" points="140,220 180,220 180,240 120,240 120,120 280,120 280,240 220,240 220,220 260,220 260,140 140,140" />
		<rect x="40" y="200" height="40" width="40" />
		<rect x="320" y="200" height="40" width="40" />
		<rect x="40" y="280" height="40" width="80" />
		<rect x="160" y="280" height="40" width="80" />
		<rect x="280" Y="280" height="40" width="80" />
		<rect x="40" y="360" height="43" width="320" />
		<!-- End of blocks -->
		
	</svg>
		<p id="win_or_lose_text"></p>
	
	<script>
		var game_screen = document.getElementById("game_screen");
		var player = document.getElementById("player");
		var mouth = document.getElementById("mouth");
		var player_x_speed = 1;
		var player_y_speed = 1;
		var player_x_pos;
		var player_y_pos;
		var total_balls_eaten = 0;
		var has_not_won = true;
		var has_not_lost = true;
		var current_key;
		var travel_direction;
		
		<?php
			for ($i = 1; $i < 25; $i++) {
				echo "var waypoint{$i}_x_pos = document.getElementById('waypoint{$i}').getAttribute('cx');\n";
				echo "var waypoint{$i}_y_pos = document.getElementById('waypoint{$i}').getAttribute('cy');\n";
			}
	
			// Arrays to tell which direction it is possible to travel from every waypoint
			$waypoint_arrays = array(
								array(),
								array(0,0,1,1), /* 1 */
								array(1,0,1,1),
								array(1,0,0,1),
								array(0,1,1,0),
								array(1,0,1,1),
								array(1,1,1,0),
								array(1,0,1,1),
								array(1,1,0,0),
								array(0,0,1,1),
								array(1,1,0,1), /* 10 */
								array(0,1,1,1),
								array(1,0,0,1),
								array(0,1,1,1),
								array(1,1,1,0),
								array(1,0,1,1),
								array(1,0,1,1),
								array(1,1,1,0),
								array(1,1,0,1),
								array(0,1,1,1),
								array(1,1,1,0), /* 20 */
								array(1,1,1,0),
								array(1,1,0,1),
								array(0,1,0,0),
								array(0,1,0,0)
							   );
		?>
		
		function main(){
			if (total_balls_eaten >= 61) {
				win();
			}
			else {
			player_x_pos = parseFloat(player.getAttribute("cx"));
			player_y_pos = parseFloat(player.getAttribute("cy"));
			
			
			// Code so that Pacman can turn around
			if (current_key == "left" && travel_direction == "right") {
				travel_direction = "left";
			}
			else if (current_key == "up" && travel_direction == "down") {
				travel_direction = "up";
			}
			else if (current_key == "right" && travel_direction == "left") {
				travel_direction = "right";
			}
			else if (current_key == "down" && travel_direction == "up") {
				travel_direction = "down";
			}
			
			// Start of if-statements that checks when pacman is at a waypoint, and they changes direction depending on which key was pressed
			else if (current_key == "left" && ((player_x_pos == waypoint2_x_pos && player_y_pos == waypoint2_y_pos)<?php
																												foreach ($waypoint_arrays as $key => $value) {
																													$waypoint_nr = $key;
																													foreach ($value as $key => $value) {
																														if ($key == 0 && $value == 1 && $waypoint_nr !== 2) {
																															echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																														}
																													}
																												}
																											  ?>)) {
				travel_direction = "left";
			}
			else if (current_key == "up" && ((player_x_pos == waypoint4_x_pos && player_y_pos == waypoint4_y_pos)<?php
																												foreach ($waypoint_arrays as $key => $value) {
																													$waypoint_nr = $key;
																													foreach ($value as $key => $value) {
																														if ($key == 1 && $value == 1 && $waypoint_nr !== 4) {
																															echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																														}
																													}
																												}
																											  ?>)) {
				travel_direction = "up";
			}
			else if (current_key == "right" && ((player_x_pos == waypoint1_x_pos && player_y_pos == waypoint1_y_pos)<?php
																												foreach ($waypoint_arrays as $key => $value) {
																													$waypoint_nr = $key;
																													foreach ($value as $key => $value) {
																														if ($key == 2 && $value == 1 && $waypoint_nr !== 1) {
																															echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																														}
																													}
																												}
																											  ?>)) {
				travel_direction = "right";
			}
			else if (current_key == "down" && ((player_x_pos == waypoint1_x_pos && player_y_pos == waypoint1_y_pos)<?php
																												foreach ($waypoint_arrays as $key => $value) {
																													$waypoint_nr = $key;
																													foreach ($value as $key => $value) {
																														if ($key == 3 && $value == 1 && $waypoint_nr !== 1) {
																															echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																														}
																													}
																												}
																											  ?>)) {
				travel_direction = "down";	
			}
			// End of if-statements for turning
			
			
			// Code to eat the balls
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
			// End of code to eat the balls
			
			
			// If-statements to stop Pacman from going into walls and such when reaching a waypoint with a direction he is not allowed to have
			if (travel_direction == "left") {
				if ((player_x_pos == waypoint1_x_pos && player_y_pos == waypoint1_y_pos)<?php
																							foreach ($waypoint_arrays as $key => $value) {
																								$waypoint_nr = $key;
																								foreach ($value as $key => $value) {
																									if ($key == 0 && $value == 0 && $waypoint_nr !== 1) {
																										echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																									}
																								}
																							}
																						?>) {
					// Do nothing
				}
				else {
					player_x_pos = player_x_pos - player_x_speed;
				}
			}
			else if (travel_direction == "up") {
				if ((player_x_pos == waypoint1_x_pos && player_y_pos == waypoint1_y_pos)<?php
																							foreach ($waypoint_arrays as $key => $value) {
																								$waypoint_nr = $key;
																								foreach ($value as $key => $value) {
																									if ($key == 1 && $value == 0 && $waypoint_nr !== 1) {
																										echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																									}
																								}
																							}
																						?>) {
					// Do nothing
				}
				else {
					player_y_pos = player_y_pos - player_y_speed;
				}
			}
			else if (travel_direction == "right") {
				if ((player_x_pos == waypoint3_x_pos && player_y_pos == waypoint3_y_pos)<?php
																							foreach ($waypoint_arrays as $key => $value) {
																								$waypoint_nr = $key;
																								foreach ($value as $key => $value) {
																									if ($key == 2 && $value == 0 && $waypoint_nr !== 3) {
																										echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																									}
																								}
																							}
																						?>) {
					// Do nothing
				}
				else {
					player_x_pos = player_x_pos + player_x_speed;
				}
			}
			else if (travel_direction == "down") {
				if ((player_x_pos == waypoint4_x_pos && player_y_pos == waypoint4_y_pos)<?php
																							foreach ($waypoint_arrays as $key => $value) {
																								$waypoint_nr = $key;
																								foreach ($value as $key => $value) {
																									if ($key == 3 && $value == 0 && $waypoint_nr !== 4) {
																										echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
																									}
																								}
																							}
																						?>) {
					// Do nothing
				}
				else {
					player_y_pos = player_y_pos + player_y_speed;
				}
			}
			// End of if-statements that stop Pacman from going into walls
			
			// Change the direction Pacman is looking when he turns around
			if (travel_direction == "left") {
				mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos - 15) + "," + (player_y_pos - 7) + " " + (player_x_pos - 15) + "," + (player_y_pos + 7));
			}
			else if (travel_direction == "up") {
				mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos - 15) + " " + (player_x_pos - 7) + "," + (player_y_pos - 15));
			}
			else if (travel_direction == "right") {
				mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 15) + "," + (player_y_pos - 7) + " " + (player_x_pos + 15) + "," + (player_y_pos + 7));
			}
			else if (travel_direction == "down") {
				mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos + 15) + " " + (player_x_pos -7) + "," + (player_y_pos + 15));
			}
			// End of Pacman's direction
			
			player.setAttribute("cx", player_x_pos);
			player.setAttribute("cy", player_y_pos);
			
			setTimeout(main, 10);
			}
		}
		
		document.onkeydown = function(event) {
		
			var e = event.keyCode;
			
			if (e==37 /* left */) {
				current_key = "left";
			}
			else if (e==38 /* up */) {
				current_key = "up";
			}
			else if (e==39 /* right */) {
				current_key = "right";
			}
			else if (e==40 /* down */) {
				current_key = "down";
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