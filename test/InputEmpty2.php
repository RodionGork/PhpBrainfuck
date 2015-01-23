<?php

require 'src/Brainfuck.php';

$prg = ',.,.,.,.';
    
$bf = new Brainfuck(true);
echo $bf->run($prg, "Zlo\n");
echo $bf->run($prg, "Bla");
