<?php

require 'src/Brainfuck.php';
    
$bf = new Brainfuck(true, 10000, true);
echo $bf->run('+++#>$-#>$-:<:<:', "");
