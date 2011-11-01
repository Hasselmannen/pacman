<?php
/**
 * A Pacman game.
 *
 * @author Johan Hasselqvist and Lars Gunther
 * 
 * Currently in a nonfunctional state since it is being rewritten
 */
 
$number_of_ghosts = 6; // Do we really need 6?
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <title>Pacman</title>
    <meta charset="utf-8" />
    <style>
        body {
            width: 609px;
            margin: auto;
            margin-top: 10px;
        }
        svg {
            background-color: black;
            border: 3px solid blue;
            border-radius: 9px;
        }
        #player circle {
            fill: yellow;
        }
        #player polygon {
            fill: black;
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
        /*
        #ball_20_20, #ball_20_380, #ball_380_20, #ball_380_380 {
            stroke-width: 5;
            stroke: white;
        }
        */
        #win_or_lose_text {
            color: white;
            position: absolute;
            top: 70px;
            width: 400px;
            font-size: 80px;
            text-align: center;
        }
        .ghost1 {
            fill: red;
            stroke: none;
        }
        .ghost2 {
            fill: blue;
            stroke: none;
        }
        .ghost3 {
            fill: green;
            stroke: none;
        }
        .ghost4 {
            fill: orange;
            stroke: none;
        }
        .ghost5 {
            fill: pink;
            stroke: none;
        }
        .ghost6 {
            fill: purple;
            stroke: none;
        }
        .ghost_eye {
            stroke: white;
            stroke-width: 2;
            fill: black;
        }
        .ghost_top, .ghost_btm {
        	fill: inherit;
            stroke: none;
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
         width="600"
         height="600"
         viewbox= "0 0 400 400"
         viewport="0 0 600 600">
      <defs>
        <g id="ghosts">
          <circle class="ghost_top" cx='120' cy='160' r='12' />
          <rect   class='ghost_btm'  x='108'  y='160' height='14' width='24' />
          <circle class='ghost_eye' cx='115' cy='157' r='2' />
          <circle class='ghost_eye' cx='125' cy='157' r='2' />
        </g>
      </defs>
                
        <!-- Start of the player -->
        <g id="player">
          <circle cx="220" cy="100" r="15" />
          <polygon points="220,100 205,93 205,107" />
        </g>
        <!-- End of player -->
        
        <?php
            // Generates the white balls
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
        
        <!-- Start of waypoints where it is possible to turn -->
        <!-- TODO: There is no need to store these in the DOM. They never get painted! -->
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

        <!-- Start of Ghosts -->
        <?php
            $xtrans = 0;
            $ytrans = 0;
            // $number_of_ghosts must be divisible by 2
            $first_half = $number_of_ghosts / 2;
            for ($ghost_nr = 1; $ghost_nr <= $first_half; $ghost_nr++) {
                $xtrans += 40;
                echo "<use xlink:href=\"#ghosts\" transform=\"translate({$xtrans},{$ytrans})\" class=\"ghost{$ghost_nr}\"/>\n";
            }
            $xtrans = 0;
            $ytrans = 35;
            for ( ; $ghost_nr <= $number_of_ghosts; $ghost_nr++) {
                $xtrans += 40;
                echo "<use xlink:href=\"#ghosts\" transform=\"translate({$xtrans},{$ytrans})\" class=\"ghost{$ghost_nr}\"/>\n";
            }
        ?>
        <!-- End of Ghosts -->
        
        
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
        var has_not_won  = true;
        var has_not_lost = true;
        var current_key;
        var player_travel_direction;
        var ghost_speed = 0.5;
        
        var ghost1_roll;
        var ghost2_roll;
        var ghost3_roll;
        var ghost4_roll;
        var ghost5_roll;
        // Why not ghost 6?
        
        // TODO: Implement proposed struvćture for waypoints
        // First of all - make all waypoints an array and not individual variables
        /*
        var waypoints = [
            {
                x_pos = 20,
                y_pos = 20,
                dirs  = array(0,0,1,1)
            }
            // repeat
        ]; // End waypoints array
        */
        <?php
            // Waypoint position
            for ($waypoint_nr = 1; $waypoint_nr < 25; $waypoint_nr++) {
                echo "var waypoint{$waypoint_nr}_x_pos = document.getElementById('waypoint{$waypoint_nr}').getAttribute('cx');\n";
                echo "var waypoint{$waypoint_nr}_y_pos = document.getElementById('waypoint{$waypoint_nr}').getAttribute('cy');\n";
            }
            // Arrays to tell which direction it is possible to travel from every waypoint
            $waypoint_arrays = array(
                array(),        /* There is no waypoint with the number 0 */
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
        //Start of Ghost variables
        // TODO: Move all of the Ghost variables into arrays
        <?php
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr}_x_min;\n";
                echo "var ghost{$ghost_nr}_x_max;\n";
                echo "var ghost{$ghost_nr}_y_min;\n";
                echo "var ghost{$ghost_nr}_y_max;\n";
            }
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr}_y_pos;\n";
            }
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr} = document.getElementById('ghost{$ghost_nr}_top');\n";
            }
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr}_x_speed = 0;\n";
            }
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr}_travel_direction = 'down';\n";
            }
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "var ghost{$ghost_nr}_exited_box = false;\n";
            }
        ?>
        // End of Ghost variables

        function main(){
            if (total_balls_eaten >= 61) {
                win();
            }
            <?php
                // Check Ghost-collision
                // TODO: Remove PHP and loop through array in JS
                for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                    echo "else if(player_x_pos <= ghost{$ghost_nr}_x_max && player_x_pos >= ghost{$ghost_nr}_x_min && player_y_pos <= ghost{$ghost_nr}_y_max && player_y_pos >= ghost{$ghost_nr}_y_min) {";
                    echo "  lose();";
                    echo "}";
                }
            ?>
            else {
            player_x_pos = parseFloat(player.getAttribute("cx"));
            player_y_pos = parseFloat(player.getAttribute("cy"));
            
            // Code so that Pacman can turn around
            if (current_key == "left" && player_travel_direction == "right") {
                player_travel_direction = "left";
            } else if (current_key == "up" && player_travel_direction == "down") {
                player_travel_direction = "up";
            } else if (current_key == "right" && player_travel_direction == "left") {
                player_travel_direction = "right";
            } else if (current_key == "down" && player_travel_direction == "up") {
                player_travel_direction = "down";
            }
            
            // Start of if-statements that checks when pacman is at a waypoint, 
            // and then changes direction depending on which key was pressed
            else if (current_key == "left" &&
                     ((player_x_pos == waypoint2_x_pos && player_y_pos == waypoint2_y_pos) <?php
                foreach ($waypoint_arrays as $key => $value) {
                    $waypoint_nr = $key;
                    foreach ($value as $key => $value) {
                        if ($key == 0 && $value == 1 && $waypoint_nr !== 2) {
                            echo " || (player_x_pos == waypoint{$waypoint_nr}_x_pos && player_y_pos == waypoint{$waypoint_nr}_y_pos)";
                        }
                    }
                }
          ?>)) {
    player_travel_direction = "left";
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
                player_travel_direction = "up";
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
                player_travel_direction = "right";
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
                player_travel_direction = "down";   
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
            if (player_travel_direction == "left") {
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
                    // TODO: Rewrite condition and remove this empty block
                }
                else {
                    player_x_pos = player_x_pos - player_x_speed;
                }
            }
            else if (player_travel_direction == "up") {
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
            else if (player_travel_direction == "right") {
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
            else if (player_travel_direction == "down") {
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
            // TODO: Transform the player grouped object instead of changing the mouth
            if (player_travel_direction == "left") {
                mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos - 15) + "," + (player_y_pos - 7) + " " + (player_x_pos - 15) + "," + (player_y_pos + 7));
            }
            else if (player_travel_direction == "up") {
                mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos - 15) + " " + (player_x_pos - 7) + "," + (player_y_pos - 15));
            }
            else if (player_travel_direction == "right") {
                mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 15) + "," + (player_y_pos - 7) + " " + (player_x_pos + 15) + "," + (player_y_pos + 7));
            }
            else if (player_travel_direction == "down") {
                mouth.setAttribute("points", player_x_pos + "," + player_y_pos + " " + (player_x_pos + 7) + "," + (player_y_pos + 15) + " " + (player_x_pos -7) + "," + (player_y_pos + 15));
            }
            // End of Pacman's direction
            
            player.setAttribute("cx", player_x_pos);
            player.setAttribute("cy", player_y_pos);

            <?php
                // Ghost-positions
                for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                    echo "ghost{$ghost_nr}_x_min = parseFloat(ghost{$ghost_nr}.getAttribute('cx')) - 27;\n";
                    echo "ghost{$ghost_nr}_x_max = parseFloat(ghost{$ghost_nr}.getAttribute('cx')) + 27;\n";
                    echo "ghost{$ghost_nr}_y_min = parseFloat(ghost{$ghost_nr}.getAttribute('cy')) - 27;\n";
                    echo "ghost{$ghost_nr}_y_max = parseFloat(ghost{$ghost_nr}.getAttribute('cy')) + parseFloat(document.getElementById('ghost{$ghost_nr}_bot').getAttribute('height')) + 15;\n";
                }
                for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                    echo "ghost{$ghost_nr}_x_pos = parseFloat(ghost{$ghost_nr}.getAttribute('cx'));\n";
                    echo "ghost{$ghost_nr}_y_pos = parseFloat(ghost{$ghost_nr}.getAttribute('cy'));\n";
                }
                // End of Ghost-positions
                
                for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                    echo "if (false) {\n";
                    echo "  ghost{$ghost_nr}_travel_direction = 'left';\n";
                    echo "}\n";
                    echo "else if (false) {\n";
                    echo "  ghost{$ghost_nr}_travel_direction = 'up';\n";
                    echo "}\n";
                    echo "else if (false) {\n";
                    echo "  ghost{$ghost_nr}_travel_direction = 'right';\n";
                    echo "}\n";
                    echo "else if (false) {\n";
                    echo "  ghost{$ghost_nr}_travel_direction = 'down';\n";
                    echo "}\n";
                }
            
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "if (ghost{$ghost_nr}_x_pos == 200 && ghost{$ghost_nr}_y_pos == 260 && ghost{$ghost_nr}_exited_box == false) {\n";
                echo "  if (Math.random() < 0.5) {\n";
                echo "      ghost{$ghost_nr}_travel_direction = 'left';\n";
                echo "  }\n";
                echo "  else {\n";
                echo "      ghost{$ghost_nr}_travel_direction = 'right';\n";
                echo "  }\n";
                echo "  ghost{$ghost_nr}_exited_box = true;\n";
                echo "}\n";
            }
    
            
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                foreach ($waypoint_arrays as $key => $value) {
                    if ($key !== 0) {
                        echo "if (ghost{$ghost_nr}_x_pos == waypoint{$key}_x_pos && ghost{$ghost_nr}_y_pos == waypoint{$key}_y_pos) {\n";
                        echo "ghost{$ghost_nr}_at_waypoint();\n";
                        echo "}\n";
                    }
                }
            }
            
            // Code moving the ghosts
            for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
                echo "if (ghost{$ghost_nr}_travel_direction == 'left') {\n";
                    echo "  ghost{$ghost_nr}.setAttribute('cx', ghost{$ghost_nr}_x_pos - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_bot').setAttribute('x', ghost{$ghost_nr}_x_pos - 12 - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cx', ghost{$ghost_nr}_x_pos - 5 - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cx', ghost{$ghost_nr}_x_pos + 5 - ghost_speed);\n";
                echo "}\n";
                echo "else if (ghost{$ghost_nr}_travel_direction == 'up') {\n";
                    echo "  ghost{$ghost_nr}.setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_bot').setAttribute('y', ghost{$ghost_nr}_y_pos - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cy', ghost{$ghost_nr}_y_pos - ghost_speed);\n";
                echo "}\n";
                echo "else if (ghost{$ghost_nr}_travel_direction == 'right') {\n";
                echo "ghost{$ghost_nr}.setAttribute('cx', ghost{$ghost_nr}_x_pos + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_bot').setAttribute('x', ghost{$ghost_nr}_x_pos - 12 + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cx', ghost{$ghost_nr}_x_pos - 5 + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cx', ghost{$ghost_nr}_x_pos + 5 + ghost_speed);\n";
                echo "}\n";
                echo "else if (ghost{$ghost_nr}_travel_direction == 'down') {\n";
                    echo "  ghost{$ghost_nr}.setAttribute('cy', ghost{$ghost_nr}_y_pos + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_bot').setAttribute('y', ghost{$ghost_nr}_y_pos + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye1').setAttribute('cy', ghost{$ghost_nr}_y_pos + ghost_speed);\n";
                    echo "  document.getElementById('ghost{$ghost_nr}_eye2').setAttribute('cy', ghost{$ghost_nr}_y_pos + ghost_speed);\n";
                echo "}\n";
            }
            ?>
            setTimeout(main, 10); // TODO: Find better value or use paint events
            }
        }
        
        document.onkeydown = function(event) {
        
            var e = event.keyCode;
            
            // TODO: This really should be switch-case
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
        
        <?php
        for ($ghost_nr = 1; $ghost_nr < ($number_of_ghosts + 1); $ghost_nr ++) {
            echo "function ghost{$ghost_nr}_at_waypoint() {\n";
            echo "  ghost{$ghost_nr}_roll = Math.random();\n";

            echo "  if (ghost{$ghost_nr}_roll <= 0.25 && ghost{$ghost_nr}_travel_direction !== 'right' && ((ghost{$ghost_nr}_x_pos == waypoint2_x_pos && ghost{$ghost_nr}_y_pos == waypoint2_y_pos)";
                foreach ($waypoint_arrays as $key => $value) {
                    $waypoint_nr = $key;
                    foreach ($value as $key => $value) {
                        if ($key == 0 && $value == 1 && $waypoint_nr !== 2) {
                            echo " || (ghost{$ghost_nr}_x_pos == waypoint{$waypoint_nr}_x_pos && ghost{$ghost_nr}_y_pos == waypoint{$waypoint_nr}_y_pos)";
                        }
                    }
                }
            echo                                                                                                      ")) {\n";
            echo "      ghost{$ghost_nr}_travel_direction = 'left';\n";
            echo "  }\n";
            echo "  else if (ghost{$ghost_nr}_roll <= 0.5 && ghost{$ghost_nr}_roll > 0.25 && (ghost{$ghost_nr}_travel_direction !== 'down' || (ghost{$ghost_nr}_y_pos == waypoint24_y_pos) && (ghost{$ghost_nr}_x_pos == waypoint23_x_pos || ghost{$ghost_nr}_x_pos == waypoint24_x_pos)) && ((ghost{$ghost_nr}_x_pos == waypoint4_x_pos && ghost{$ghost_nr}_y_pos == waypoint4_y_pos)";
                foreach ($waypoint_arrays as $key => $value) {
                    $waypoint_nr = $key;
                    foreach ($value as $key => $value) {
                        if ($key == 1 && $value == 1 && $waypoint_nr !== 4) {
                            echo " || (ghost{$ghost_nr}_x_pos == waypoint{$waypoint_nr}_x_pos && ghost{$ghost_nr}_y_pos == waypoint{$waypoint_nr}_y_pos)";
                        }
                    }
                }
            echo                                                                                                      ")) {\n";
            echo "      ghost{$ghost_nr}_travel_direction = 'up';\n";
            echo "  }\n";
            echo "  else if (ghost{$ghost_nr}_roll <= 0.75 && ghost{$ghost_nr}_roll > 0.5 && ghost{$ghost_nr}_travel_direction !== 'left' && ((ghost{$ghost_nr}_x_pos == waypoint1_x_pos && ghost{$ghost_nr}_y_pos == waypoint1_y_pos)";
                foreach ($waypoint_arrays as $key => $value) {
                    $waypoint_nr = $key;
                    foreach ($value as $key => $value) {
                        if ($key == 2 && $value == 1 && $waypoint_nr !== 1) {
                            echo " || (ghost{$ghost_nr}_x_pos == waypoint{$waypoint_nr}_x_pos && ghost{$ghost_nr}_y_pos == waypoint{$waypoint_nr}_y_pos)";
                        }
                    }
                                                                                                    }
            echo                                                                                                      ")) {\n";
            echo "      ghost{$ghost_nr}_travel_direction = 'right';\n";
            echo "  }\n";
            echo "  else if (ghost{$ghost_nr}_roll <= 1 && ghost{$ghost_nr}_roll > 0.75 && ghost{$ghost_nr}_travel_direction !== 'up' && ((ghost{$ghost_nr}_x_pos == waypoint1_x_pos && ghost{$ghost_nr}_y_pos == waypoint1_y_pos)";
                foreach ($waypoint_arrays as $key => $value) {
                    $waypoint_nr = $key;
                    foreach ($value as $key => $value) {
                        if ($key == 3 && $value == 1 && $waypoint_nr !== 1) {
                            echo " || (ghost{$ghost_nr}_x_pos == waypoint{$waypoint_nr}_x_pos && ghost{$ghost_nr}_y_pos == waypoint{$waypoint_nr}_y_pos)";
                        }
                    }
                }
            echo                                                                                                      ")) {\n";
            echo "      ghost{$ghost_nr}_travel_direction = 'down';\n"; 
            echo "  }\n";
            echo "  else {\n";
            echo "      ghost{$ghost_nr}_at_waypoint();\n";
                echo "}\n";
            echo "}\n";
        }
        ?>

        // The next row executes main and assigns the RETURN value as an event handler
        // TODO: Either rewrite as 
        // window.onload = main;
        // or just
        // main();
        // Or better yet - make main self executable
        window.onload = main();
    </script>
</body>
</html>