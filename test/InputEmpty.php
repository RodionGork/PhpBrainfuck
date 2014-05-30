<?php

require '/src/Brainfuck.php';

$prg = ';>;[-<+>]<:';
    
$bf = new Brainfuck(true);
echo $bf->run($prg, "");
