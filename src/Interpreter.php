<?php

require pathinfo(__FILE__, PATHINFO_DIRNAME) . '/Brainfuck.php';

$prog = file_get_contents($argv[1]);
$input = file_get_contents('php://stdin');

$bf = new Brainfuck(true);
echo $bf->run($prog, $input);
