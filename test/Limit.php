<?php

require '/src/Brainfuck.php';

$prg = '+[+:]';
    
$bf = new Brainfuck(true, 50);

try {
    echo $bf->run($prg, "");
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
}