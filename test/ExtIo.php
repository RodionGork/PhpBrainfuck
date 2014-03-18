<?php

require '/src/Brainfuck.php';
    
$bf = new Brainfuck(true);
echo $bf->run(';>;[-<+>]<:', "3 5\n");
