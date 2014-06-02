<?php

require 'src/Brainfuck.php';

$bf = new Brainfuck(true);

$x = rand(5, 9);

$bf->run(';[->+>+<<]>>[-<<+>>]', "$x");

echo $bf->getDataCell(0) . " " . $bf->getDataCell(1) . "\n";

