<?php

require 'src/Brainfuck.php';

$prg = ';>;[-<+>]<:';
    
$bf = new Brainfuck(true);
echo $bf->run($prg, "3 5\n");

$bf->extio = false;
echo $bf->run($prg, "3 5\n") === "";
