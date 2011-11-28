<?php
/**
 * A Pacman game in Scalable Vector Graphics.
 * 
 * @author Johan Hasselqvist and Lars Gunther.
 */

if ( isset($_GET['ghosts']) ) {
		$number_of_ghosts = (int)$_GET['ghosts'];
	}
	else {
		$number_of_ghosts = 6;
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Pacman</title>
	<meta charset="utf-8" />
	<style>
		body {
			width: 559px;
			margin: 10px auto;
		}
		svg {
			background-color: black;
			border: 3px solid blue;
			border-radius: 9px;
		}
		#player circle {
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
		.ghost_btm {
			stroke: none;
		}
		.ghost_eye {
			fill: black;
			stroke: white;
			stroke-width: 2;
		}
		#ghost1 {
			fill: red;
		}
		#ghost2 {
			fill: cyan;
		}
		#ghost3 {
			fill: green;
		}
		#ghost4 {
			fill: pink;
		}
		#ghost5 {
			fill: orange;
		}
		#win_or_lose_text {
			color: white;
			position: absolute;
			top: 100px;
			width: 559px;
			font-size: 100px;
			text-align: center;
		}
		.ball {
			fill: white;
		}
		.ghosts {
			fill: purple;
		}
	</style>
</head>
<body>
	<svg id="game_screen"
		 version="1.1"
		 baseProfile="full"
		 width="550"
		 height="550"
		 viewbox="0 0 400 400">
         <!-- OK without viewport ? -->
		
		<defs>
			<g id="ghosts">
				<rect   class="ghost_btm"  x="-12" y="0" height="14" width="24" />
				<circle class="ghost_top" cx="0"  cy="0"  r="12" />
				<circle class="ghost_eye" cx="-5" cy="-3" r="2" />
				<circle class="ghost_eye" cx="5"  cy="-3" r="2" />
			</g>
		</defs>
		
		<!-- Generates the white balls -->
		<?php
			// TODO: Only generate the ones we actually need
			for ($y = 20; $y < 400; $y = $y + 40) {
				echo "\t\t<circle id='ball_20_{$y}' class='ball' cx='20' cy='{$y}' r='4' />\n";
				for ($x = 60; $x <= 400; $x = $x + 40) {
					if (!($x >= 120 && $x <= 260 && $y >= 120 && $y <= 240)) {
						echo "\t\t<circle id='ball_{$x}_{$y}' class='ball' cx='{$x}' cy='{$y}' r='4' />\n";
					}
				}
			}
		?>
		<!-- End of generating white balls -->
		
		<!-- Start of the player -->
		<g id="player" transform="translate(220,100) rotate(0)">
			<circle cx="0" cy="0" r="15" />
			<polygon id="mouth" points="0,0 -16,-7 -16,7" />
		</g>
		<!-- End of player -->
		
		<!-- Start of the blocks on the map -->
		<rect x="40" y="40" height="40" width="160" />
		<rect x="240" y="40" height="40" width="120" />
		<rect x="-3" y="120" height="40" width="83" />
		<rect x="320" y="120" height="40" width="83" />
		<polygon id="ghost_box" points="140,220 184,220 184,240 120,240 120,120 280,120 280,240 216,240 216,220 260,220 260,140 140,140" />
		<rect x="40" y="200" height="40" width="40" />
		<rect x="320" y="200" height="40" width="40" />
		<rect x="40" y="280" height="40" width="80" />
		<rect x="160" y="280" height="40" width="80" />
		<rect x="280" Y="280" height="40" width="80" />
		<rect x="40" y="360" height="43" width="320" />
		<!-- End of blocks -->
		
		<!-- Generate the ghosts -->
		<?php
			// TODO: Fix the spawning of ghosts so that they do not walk into the same pixels
			$xtrans = 0;
			
			for($ghost_nr = 1; $ghost_nr <= $number_of_ghosts && $ghost_nr <= 3; $ghost_nr++) {
				$xtrans += 40;
				echo "<use xlink:href='#ghosts' transform='translate(" . (120 + $xtrans) . "," . (160) . ")' x='0' y='0' width='24' height='26' id='ghost{$ghost_nr}' class='ghosts' />\n";
			}
			
			$xtrans = 0;
			
			for ( ; $ghost_nr <= $number_of_ghosts && $ghost_nr <= 6; $ghost_nr ++) {
				$xtrans += 40;
				echo "<use xlink:href='#ghosts' transform='translate(" . (120 + $xtrans) . "," . (196) . ")' x='0' y='0' width='24' height='26' id='ghost{$ghost_nr}' class='ghosts' />\n";
			}
			
			for ( ; $ghost_nr <= $number_of_ghosts; $ghost_nr++) {
				$xtrans = rand(20, 60) * 2;
				$ytrans = rand(0, 18) * 2;
				echo "<use xlink:href='#ghosts' transform='translate(" . (120 + $xtrans) . "," . (160 + $ytrans) . ")' x='0' y='0' width='24' height='26' id='ghost{$ghost_nr}' class='ghosts' />\n";
			}
		?>
		<!-- End of generating the ghosts -->
		
	</svg>
		<p id="win_or_lose_text"></p>
		<p id="score_text"></p>
	
	<script>
		// The balls that make the ghosts edible
		document.getElementById("ball_20_20").setAttribute("r", 7);
		document.getElementById("ball_20_380").setAttribute("r", 7);
		document.getElementById("ball_380_20").setAttribute("r", 7);
		document.getElementById("ball_380_380").setAttribute("r", 7);
		// End of the balls that make the ghosts edible
	
		// Player variables
		var player = document.getElementById("player");
		var mouth = document.getElementById("mouth");
		var player_info;
		var player_pos;
		var current_key;
		var player_travel_dir;
		var player_speed = 2;
		var opening_mouth = false;
		var mouth_width = 0;
		var eaten_by_ghosts = false;
		var total_balls_eaten = 0;
		var score = 0;
		// End of player variables
		
		// Ghost variables
		var ghosts = new Array();
		var ghosts_info = new Array();
		var ghosts_pos = new Array();
		var ghosts_exited_box = new Array();
		var ghosts_travel_dir = new Array();
		var ghosts_choose_dir = new Array();
		var ghost_speed = 2;
		var ghosts_colors = new Array();
		var edible_ghosts = new Array();
		var edible_ghosts_timers = new Array();
		var edible_ghosts_length = 500;

		for (ghost_nr = 1; ghost_nr <= <?php echo $number_of_ghosts; ?>; ghost_nr++) {
			ghosts[ghost_nr] = document.getElementById("ghost" + ghost_nr);
			ghosts_colors[ghost_nr] = 'purple';
			edible_ghosts[ghost_nr] = false;
			edible_ghosts_timers[ghost_nr] = 0;
		}
		
		ghosts_colors[1] = 'red';
		ghosts_colors[2] = 'cyan';
		ghosts_colors[3] = 'green';
		ghosts_colors[4] = 'pink';
		ghosts_colors[5] = 'orange';
		// End of ghost variables
		
		// Misc variables
		var game_screen = document.getElementById("game_screen");
		var win_or_lose_text = document.getElementById("win_or_lose_text");
		var regexp = /\((\d{1,3}),?\s?(\d{1,3})\)/;
		// End of misc variables
		
		// Waypoint arrays
		<?php
			$waypoint_arrays = array(
				array(
					"x_pos" => 20,
					"y_pos" => 20,
					"dirs"  => array(0,0,1,1)
				),
				array(
					"x_pos"=> 220,
					"y_pos"=> 20,
					"dirs" => array(1,0,1,1)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 20,
					"dirs" => array(1,0,0,1)
				),
				array(
					"x_pos"=> 20,
					"y_pos"=> 100,
					"dirs" => array(0,1,1,0)
				),
				array(
					"x_pos"=> 100,
					"y_pos"=> 100,
					"dirs" => array(1,0,1,1)
				),
				array(
					"x_pos"=> 220,
					"y_pos"=> 100,
					"dirs" => array(1,1,1,0)
				),
				array(
					"x_pos"=> 300,
					"y_pos"=> 100,
					"dirs" => array(1,0,1,1)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 100,
					"dirs" => array(1,1,0,0)
				),
				array(
					"x_pos"=> 20,
					"y_pos"=> 180,
					"dirs" => array(0,0,1,1)
				),
				array(
					"x_pos"=> 100,
					"y_pos"=> 180,
					"dirs" => array(1,1,0,1)
				),
				array(
					"x_pos"=> 300,
					"y_pos"=> 180,
					"dirs" => array(0,1,1,1)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 180,
					"dirs" => array(1,0,0,1)
				),
				array(
					"x_pos"=> 20,
					"y_pos"=> 260,
					"dirs" => array(0,1,1,1)
				),
				array(
					"x_pos"=> 100,
					"y_pos"=> 260,
					"dirs" => array(1,1,1,0)
				),
				array(
					"x_pos"=> 140,
					"y_pos"=> 260,
					"dirs" => array(1,0,1,1)
				),
				array(
					"x_pos"=> 260,
					"y_pos"=> 260,
					"dirs" => array(1,0,1,1)
				),
				array(
					"x_pos"=> 300,
					"y_pos"=> 260,
					"dirs" => array(1,1,1,0)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 260,
					"dirs" => array(1,1,0,1)
				),
				array(
					"x_pos"=> 20,
					"y_pos"=> 340,
					"dirs" => array(0,1,1,1)
				),
				array(
					"x_pos"=> 140,
					"y_pos"=> 340,
					"dirs" => array(1,1,1,0)
				),
				array(
					"x_pos"=> 260,
					"y_pos"=> 340,
					"dirs" => array(1,1,1,0)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 340,
					"dirs" => array(1,1,0,1)
				),
				array(
					"x_pos"=> 20,
					"y_pos"=> 380,
					"dirs" => array(0,1,0,0)
				),
				array(
					"x_pos"=> 380,
					"y_pos"=> 380,
					"dirs" => array(0,1,0,0)
				),
			);
		?>
		// End of waypoint arrays
		
		/* **************************
			 START OF MAIN FUNCTION
		   ************************** */
		function main() {
			document.getElementById("score_text").innerHTML = "Number of ghosts eaten: " + score;
			
			if (total_balls_eaten == 61) {
				win_or_lose_text.innerHTML = "You won";
			}
			else if (eaten_by_ghosts == true) {
				win_or_lose_text.innerHTML = "Game over";
			}
			else {
				/* **************************
						START OF PLAYER
				   ************************** */
				   
				// Check player-position
				player_info = player.getAttribute("transform");
				player_pos = player_info.match(regexp);
				player_pos[1] = parseFloat(player_pos[1]);
				player_pos[2] = parseFloat(player_pos[2]);
				// End of checking player-position
				
				// Code so that Pacman can instantly turn around
				if (current_key == "left" && player_travel_dir == "right") {
					player_travel_dir = "left";
				}
				else if (current_key == "up" && player_travel_dir == "down") {
					player_travel_dir = "up";
				}
				else if (current_key == "right" && player_travel_dir == "left") {
					player_travel_dir = "right";
				}
				else if (current_key == "down" && player_travel_dir == "up") {
					player_travel_dir = "down";
				}
				// End of code so that pacman can turn around
				
				// Start of if-statements that checks when pacman is at a waypoint, and then changes dir depending on which key was pressed
				<?php
					echo "else if (current_key == 'left' && ((player_pos[1] == {$waypoint_arrays[1]['x_pos']} && player_pos[2] == {$waypoint_arrays[1]['y_pos']})\n";
						foreach ($waypoint_arrays as $key => $value) {
							$waypoint_nr = $key;
							foreach ($value["dirs"] as $key => $value) {
								if ($key == 0 && $value == 1 && $waypoint_nr !== 1) {
									echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
								}
							}
						}
				?>)) {
					player_travel_dir = "left";
				}
				<?php
					echo "else if (current_key == 'up' && ((player_pos[1] == {$waypoint_arrays[3]['x_pos']} && player_pos[2] == {$waypoint_arrays[3]['y_pos']})\n";
						foreach ($waypoint_arrays as $key => $value) {
							$waypoint_nr = $key;
							foreach ($value["dirs"] as $key => $value) {
								if ($key == 1 && $value == 1 && $waypoint_nr !== 3) {
									echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
								}
							}
						}
				?>)) {
					player_travel_dir = "up";
				}
				<?php
					echo "else if (current_key == 'right' && ((player_pos[1] == {$waypoint_arrays[0]['x_pos']} && player_pos[2] == {$waypoint_arrays[0]['y_pos']})\n";
						foreach ($waypoint_arrays as $key => $value) {
							$waypoint_nr = $key;
							foreach ($value["dirs"] as $key => $value) {
								if ($key == 2 && $value == 1 && $waypoint_nr !== 0) {
									echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
								}
							}
						}
				?>)) {
					player_travel_dir = "right";
				}
				<?php
					echo "else if (current_key == 'down' && ((player_pos[1] == {$waypoint_arrays[0]['x_pos']} && player_pos[2] == {$waypoint_arrays[0]['y_pos']})\n";
						foreach ($waypoint_arrays as $key => $value) {
							$waypoint_nr = $key;
							foreach ($value["dirs"] as $key => $value) {
								if ($key == 3 && $value == 1 && $waypoint_nr !== 0) {
									echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
								}
							}
						}
				?>)) {
					player_travel_dir = "down";	
				}
				// End of if-statements for turning
				
				// If-statements to stop Pacman from going into walls and such when reaching a waypoint with a dir he is not allowed to have
				if (player_travel_dir == "left") {
					<?php
						echo "if (!((player_pos[1] == {$waypoint_arrays[0]['x_pos']} && player_pos[2] == {$waypoint_arrays[0]['y_pos']})\n";
							foreach ($waypoint_arrays as $key => $value) {
								$waypoint_nr = $key;
								foreach ($value["dirs"] as $key => $value) {
									if ($key == 0 && $value == 0 && $waypoint_nr !== 0) {
										echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
									}
								}
							}
					?>)) {
						player.setAttribute("transform", "translate(" + (player_pos[1] - player_speed) + "," + (player_pos[2]) + ") rotate(0)");
					}
				}
				else if (player_travel_dir == "up") {
					<?php
						echo "if (!((player_pos[1] == {$waypoint_arrays[0]['x_pos']} && player_pos[2] == {$waypoint_arrays[0]['y_pos']})\n";
							foreach ($waypoint_arrays as $key => $value) {
								$waypoint_nr = $key;
								foreach ($value["dirs"] as $key => $value) {
									if ($key == 1 && $value == 0 && $waypoint_nr !== 0) {
										echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
									}
								}
							}
					?>)) {
						player.setAttribute("transform", "translate(" + (player_pos[1]) + "," + (player_pos[2] - player_speed) + ") rotate(90)");
					}
				}
				else if (player_travel_dir == "right") {
					<?php
						echo "if (!((player_pos[1] == {$waypoint_arrays[2]['x_pos']} && player_pos[2] == {$waypoint_arrays[2]['y_pos']})\n";
							foreach ($waypoint_arrays as $key => $value) {
								$waypoint_nr = $key;
								foreach ($value["dirs"] as $key => $value) {
									if ($key == 2 && $value == 0 && $waypoint_nr !== 2) {
										echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
									}
								}
							}
					?>)) {
						player.setAttribute("transform", "translate(" + (player_pos[1] + player_speed) + "," + (player_pos[2]) + ") rotate(180)");
					}
				}
				else if (player_travel_dir == "down") {
					<?php
						echo "if (!((player_pos[1] == {$waypoint_arrays[3]['x_pos']} && player_pos[2] == {$waypoint_arrays[3]['y_pos']})\n";
							foreach ($waypoint_arrays as $key => $value) {
								$waypoint_nr = $key;
								foreach ($value["dirs"] as $key => $value) {
									if ($key == 3 && $value == 0 && $waypoint_nr !== 3) {
										echo " || (player_pos[1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && player_pos[2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
									}
								}
							}
					?>)) {
						player.setAttribute("transform", "translate(" + (player_pos[1]) + "," + (player_pos[2] + player_speed) + ") rotate(270)");
					}
				}
				// End of if-statements that stop Pacman from going into walls
				
				// Animated mouth
				if(opening_mouth == true) {
					mouth_width = mouth_width + 1.5;
					if(mouth_width >= 20) {
						opening_mouth = false;
					}
				}
				else {
					mouth_width = mouth_width - 1.5;
					if(mouth_width <= 0) {
						opening_mouth = true;
					}
				}
				mouth.setAttribute("points", ("0,0 -16," + (-mouth_width / 2) +  " -16," + (mouth_width / 2)));
				// End of animated mouth
				
				
				// Code to eat the balls, if it is a "special" ball, make the ghosts edible
				for (y = 20; y <= 400; y = y + 40) {
					for (x = 60; x <= 400; x = x + 40) {
						if (player_pos[1] <= x + 10 && player_pos[1] >= x - 10 && player_pos[2] <= y + 10 && player_pos[2] >= y - 10 && document.getElementById("ball_" + x + "_" + y) != null ) {
							var ball_to_eat = document.getElementById("ball_" + x + "_" + y);
							ball_to_eat.parentNode.removeChild(ball_to_eat);
							total_balls_eaten ++;
							// UNDER MODIFICATION
							if (ball_to_eat.getAttribute("r") > 5) {
								for (ghost_nr = 1; ghost_nr <= <?php echo $number_of_ghosts; ?>; ghost_nr++) {
									edible_ghosts[ghost_nr] = true;
									edible_ghosts_timers[ghost_nr] = 0;
								}
							}							
							
						}
					}
					if (player_pos[1] <= 30 && player_pos[1] >= 10 && player_pos[2] <= y + 10 && player_pos[2] >= y - 10 && document.getElementById("ball_20_" + y) != null ) {
						var ball_to_eat = document.getElementById("ball_20_" + y);
						ball_to_eat.parentNode.removeChild(ball_to_eat);
						total_balls_eaten ++;
						if (ball_to_eat.getAttribute("r") > 5) {
							for (ghost_nr = 1; ghost_nr <= <?php echo $number_of_ghosts; ?>; ghost_nr++) {
								edible_ghosts[ghost_nr] = true;
								edible_ghosts_timers[ghost_nr] = 0;
							}
						}				
					}
				}
				// End of code to eat the balls
				
				
				/* **************************
					     END OF PLAYER
				   ************************** */
				
				
				/* **************************
						START OF GHOSTS
				   ************************** */
				
				for (ghost_nr = 1; ghost_nr <= <?php echo $number_of_ghosts; ?>; ghost_nr++) {
				
					// Code to make the ghosts edible when pacman is powered up
					if (edible_ghosts[ghost_nr] == true) {
						ghosts[ghost_nr].style.fill = 'blue';
						if (edible_ghosts_timers[ghost_nr] > edible_ghosts_length - 10) {
							ghosts[ghost_nr].style.fill = '#AAF';
						}
						else if (edible_ghosts_timers[ghost_nr] > edible_ghosts_length - 20) {
							ghosts[ghost_nr].style.fill = 'blue';
						}
						else if (edible_ghosts_timers[ghost_nr] > edible_ghosts_length - 30) {
							ghosts[ghost_nr].style.fill = '#AAF';
						}
						else if (edible_ghosts_timers[ghost_nr] > edible_ghosts_length - 40) {
							ghosts[ghost_nr].style.fill = 'blue';
						}
						else if (edible_ghosts_timers[ghost_nr] > edible_ghosts_length - 50) {
							ghosts[ghost_nr].style.fill = '#AAF';
						}
					}
					edible_ghosts_timers[ghost_nr]++;
					if (edible_ghosts_timers[ghost_nr] >= edible_ghosts_length) {
						edible_ghosts[ghost_nr] = false;
						ghosts[ghost_nr].style.fill = ghosts_colors[ghost_nr];
						edible_ghosts_timers[ghost_nr] = 0;
					}
					// End of making the ghosts edible
				
				
					// Check ghost-positions
					ghosts_info[ghost_nr] = ghosts[ghost_nr].getAttribute("transform");
					ghosts_pos[ghost_nr] = ghosts_info[ghost_nr].match(regexp);
					ghosts_pos[ghost_nr][1] = parseFloat(ghosts_pos[ghost_nr][1]);
					ghosts_pos[ghost_nr][2] = parseFloat(ghosts_pos[ghost_nr][2]);
					// End of checking ghost-positions
					
					// Code to make the ghosts first get out of the box and then turn at waypoints
					if (ghosts_exited_box[ghost_nr] == true) {
						// TODO: Combine this with the function that makes ghosts choose direction at waypoints, instead of having very similar code at multiple places
						<?php echo "if ((ghosts_pos[ghost_nr][1] == 20 && ghosts_pos[ghost_nr][2] == 20)";
							foreach ($waypoint_arrays as $key => $value) {
								if ($key !== 0) {
									echo " || (ghosts_pos[ghost_nr][1] == {$waypoint_arrays[$key]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[$key]['y_pos']})"; 
								}							
							}
						?>) {
							ghosts_at_waypoint(ghost_nr);
						}
					}
					else {
						if (ghosts_pos[ghost_nr][2] == 260) {
							if (Math.random() > 0.5) {
								ghosts_travel_dir[ghost_nr] = "right";
							}
							else {
								ghosts_travel_dir[ghost_nr] = "left";
							}
							ghosts_exited_box[ghost_nr] = true;
						}
						else if (ghosts_pos[ghost_nr][1] < 200) {
							ghosts_travel_dir[ghost_nr] = "right";
						}
						else if (ghosts_pos[ghost_nr][1] > 200) {
							ghosts_travel_dir[ghost_nr] = "left";
						}
						else {
							ghosts_travel_dir[ghost_nr] = "down";
						}
					}
					// End of code to make the ghosts turn
					
					// Check if Pacman collides with a ghost
					if (ghosts_pos[ghost_nr][1] < (player_pos[1] + 10) && ghosts_pos[ghost_nr][1] > (player_pos[1] - 10) && ghosts_pos[ghost_nr][2] < (player_pos[2] + 10) && ghosts_pos[ghost_nr][2] > (player_pos[2] - 10)) {
						if (edible_ghosts[ghost_nr] == true) {
							ghosts_pos[ghost_nr][1] = 200;
							ghosts_pos[ghost_nr][2] = 200;
							ghosts_exited_box[ghost_nr] = false;
							ghosts[ghost_nr].style.fill = ghosts_colors[ghost_nr];
							edible_ghosts[ghost_nr] = false;
							edible_ghosts_timers[ghost_nr] = 0;
							score ++;
						}
						else {
							eaten_by_ghosts = true;
						}
					}
					// End of pacman-ghost collision
					
					
					// Ghosts' movement
					if (ghosts_travel_dir[ghost_nr] == "left") {
						ghosts[ghost_nr].setAttribute("transform", "translate(" + (ghosts_pos[ghost_nr][1] - ghost_speed) + "," + (ghosts_pos[ghost_nr][2]) + ")");
					}
					else if (ghosts_travel_dir[ghost_nr] == "up") {
						ghosts[ghost_nr].setAttribute("transform", "translate(" + (ghosts_pos[ghost_nr][1]) + "," + (ghosts_pos[ghost_nr][2] - ghost_speed) + ")");
					}
					else if (ghosts_travel_dir[ghost_nr] == "right") {
						ghosts[ghost_nr].setAttribute("transform", "translate(" + (ghosts_pos[ghost_nr][1] + ghost_speed) + "," + (ghosts_pos[ghost_nr][2]) + ")");
					}
					else {
						ghosts[ghost_nr].setAttribute("transform", "translate(" + (ghosts_pos[ghost_nr][1]) + "," + (ghosts_pos[ghost_nr][2] + ghost_speed) + ")");
					}
					// End of ghosts' movement
					
				}
				
				/* **************************
						 END OF GHOSTS
				   ************************** */
								
				setTimeout(main, 20);
			}
		}
		/* **************************
			  END OF MAIN FUNCTION
		   ************************** */
		
		// Key-pressing function
		document.onkeydown = function(event) {
		
			var e = event.keyCode;
			
			switch(e) {
				case 37:
					current_key = "left";
					break;
				case 38:
					current_key = "up";
					break;
				case 39:
					current_key = "right";
					break;
				case 40:
					current_key = "down";
					break;
				default:
			}
		}
		// End of key-pressing function
		
		// Function to make the ghosts choose direction when at a waypoint
		function ghosts_at_waypoint(ghost_nr) {
			ghosts_choose_dir[ghost_nr] = Math.random();
			
			// TODO: Combine this with the code that checks when a ghost is at a waypoint, instead of having very similar code at multiple places
			<?php
				echo "if (ghosts_choose_dir[ghost_nr] < 0.25 && ghosts_travel_dir[ghost_nr] !== 'right' && ((ghosts_pos[ghost_nr][1] == {$waypoint_arrays[1]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[1]['y_pos']})\n";
					foreach ($waypoint_arrays as $key => $value) {
						$waypoint_nr = $key;
						foreach ($value["dirs"] as $key => $value) {
							if ($key == 0 && $value == 1 && $waypoint_nr !== 1) {
								echo " || (ghosts_pos[ghost_nr][1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
							}
						}
					}
			?>)) {
				ghosts_travel_dir[ghost_nr] = "left";
			}
			<?php
				echo "else if (ghosts_choose_dir[ghost_nr] <= 0.5 && ghosts_choose_dir[ghost_nr] > 0.25 && (ghosts_travel_dir[ghost_nr] !== 'down' || ghosts_pos[ghost_nr][2] == 380) && ((ghosts_pos[ghost_nr][1] == {$waypoint_arrays[3]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[3]['y_pos']})\n";
					foreach ($waypoint_arrays as $key => $value) {
						$waypoint_nr = $key;
						foreach ($value["dirs"] as $key => $value) {
							if ($key == 1 && $value == 1 && $waypoint_nr !== 3) {
								echo " || (ghosts_pos[ghost_nr][1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
							}
						}
					}
			?>)) {
				ghosts_travel_dir[ghost_nr] = "up";
			}
			<?php
				echo "else if (ghosts_choose_dir[ghost_nr] <= 0.75 && ghosts_choose_dir[ghost_nr] > 0.5 && ghosts_travel_dir[ghost_nr] !== 'left' && ((ghosts_pos[ghost_nr][1] == {$waypoint_arrays[0]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[0]['y_pos']})\n";
					foreach ($waypoint_arrays as $key => $value) {
						$waypoint_nr = $key;
						foreach ($value["dirs"] as $key => $value) {
							if ($key == 2 && $value == 1 && $waypoint_nr !== 0) {
								echo " || (ghosts_pos[ghost_nr][1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
							}
						}
					}
			?>)) {
				ghosts_travel_dir[ghost_nr] = "right";
			}
			<?php
				echo "else if (ghosts_choose_dir[ghost_nr] <= 1 && ghosts_choose_dir[ghost_nr] > 0.75 && ghosts_travel_dir[ghost_nr] !== 'up' && ((ghosts_pos[ghost_nr][1] == {$waypoint_arrays[0]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[0]['y_pos']})\n";
					foreach ($waypoint_arrays as $key => $value) {
						$waypoint_nr = $key;
						foreach ($value["dirs"] as $key => $value) {
							if ($key == 3 && $value == 1 && $waypoint_nr !== 0) {
								echo " || (ghosts_pos[ghost_nr][1] == {$waypoint_arrays[$waypoint_nr]['x_pos']} && ghosts_pos[ghost_nr][2] == {$waypoint_arrays[$waypoint_nr]['y_pos']})";
							}
						}
					}
			?>)) {
				ghosts_travel_dir[ghost_nr] = "down";	
			}
			else {
				ghosts_at_waypoint(ghost_nr);
			}
		}
		// End of function to make the ghosts chose direction at a waypoint
			
		main();
	</script>
</body>
</html>