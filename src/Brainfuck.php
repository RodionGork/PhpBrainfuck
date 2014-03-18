<?php

class Brainfuck {
    
    private static $commands = '><+-.,[]';
    
    function run($prg, $input) {
        $code = $this->compile($prg);
        return $this->execute($code, $input);
    }
    
    private function compile($prg) {
        $prg = $this->preprocess($prg);
        $code = $this->firstPass($prg);
        $this->secondPass($code);
        return $code;
    }
    
    private function preprocess($prg) {
        $p = preg_replace('/[^\<\>\+\-\.\,\[\]]/', '', $prg);
        return $p;
    }
    
    private function firstPass($prg) {
        $prg .= 'Z';
        $code = array();
        $i = 0;
        while ($prg[$i] != 'Z') {
            $cur = $prg[$i];
            $idx = strpos(self::$commands, $cur);
            if ($idx < 6) {
                $i0 = $i;
                while ($prg[$i] == $cur) {
                    $i++;
                }
                $code[] = $idx + (($i - $i0) << 4);
            } else {
                $code[] = $idx;
                $i++;
            }
        }
        $code[] = -1;
        return $code;
    }
    
    private function secondPass(&$code) {
        $len = count($code);
        $stack = array();
        for ($i = 0; $i < $len; $i++) {
            $cur = $code[$i];
            if ($cur == 6) {
                $stack[] = $i;
            } else if ($cur == 7) {
                $start = array_pop($stack);
                $diff = $i - $start;
                $code[$i] |= $diff << 4;
                $code[$start] |= $diff << 4;
            }
        }
    }
    
    private function execute($code, $input) {
        $data = array_fill(0, 30000, 0);
        $output = array();
        $cp = 0;
        $dp = 0;
        $ip = 0;
        while (true) {
            $cur = $code[$cp];
            if ($cur == -1) {
                break;
            }
            $cnt = $cur >> 4;
            $cmd = $cur & 0xF;
            switch ($cmd) {
                case 0:
                    $dp += $cnt;
                    break;
                case 1:
                    $dp -= $cnt;
                    break;
                case 2:
                    $data[$dp] += $cnt;
                    break;
                case 3:
                    $data[$dp] -= $cnt;
                    break;
                case 4:
                    $output[] = chr($data[$dp]);
                    break;
                case 5:
                    $data[$dp] = ord($input[$ip]);
                    break;
                case 6:
                    if ($data[$dp] == 0) {
                        $cp += $cnt;
                    }
                    break;
                case 7:
                    if ($data[$dp] != 0) {
                        $cp -= $cnt;
                    }
                    break;
            }
            $cp++;
        }
        return implode('', $output);
    }

}

// standalone test
if ($_SERVER['PHP_SELF'] == 'Brainfuck.php') {
    $bf = new Brainfuck();
    echo $bf->run('++++++++[>++++[>++>+++>+++>+<<<<-]>+>+>->>+[<]<-]>>.>---.+++++++..+++.>>.<-.<.+++.------.--------.>>+.>++.', "");
}

