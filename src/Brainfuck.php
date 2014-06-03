<?php

class Brainfuck {
    
    private static $commands = '><+-.,:;[]';

    public $extio;
    public $limit;
    public $data; 
    
    function __construct($extio = false, $limit = 100000) {
        $this->extio = $extio;
        $this->limit = $limit;
        $this->data = null;
    }

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
        $pattern = $this->extio ? '/[^\<\>\+\-\.\,\:\;\[\]]/' : '/[^\<\>\+\-\.\,\[\]]/';
        $p = preg_replace($pattern, '', $prg);
        return $p;
    }

    function codeLength($prg) {
        return strlen($this->preprocess($prg));
    }
    
    private function firstPass($prg) {
        $prg .= 'Z';
        $code = array();
        $i = 0;
        while ($prg[$i] != 'Z') {
            $cur = $prg[$i];
            $idx = strpos(self::$commands, $cur);
            if ($idx < 8) {
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
            if ($cur == 8) {
                $stack[] = $i;
            } else if ($cur == 9) {
                $start = array_pop($stack);
                $diff = $i - $start;
                $code[$i] |= $diff << 4;
                $code[$start] |= $diff << 4;
            }
        }
    }
    
    private function execute($code, $input) {
        $counter = $this->limit;
        $data = array_fill(0, 30000, 0);
        $output = array();
        $cp = 0;
        $dp = 0;
        $ip = 0;
        while ($counter >= 0) {
            $cur = $code[$cp];
            if ($cur == -1) {
                break;
            }
            $cnt = $cur >> 4;
            $cmd = $cur & 0xF;
            if ($cmd <= 3 || $cmd >= 8) {
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
                    case 8:
                        if ($data[$dp] == 0) {
                            $cp += $cnt;
                        }
                        break;
                    case 9:
                        if ($data[$dp] != 0) {
                            $cp -= $cnt;
                        }
                        break;
                }
                $counter--;
            } else {
                while ($cnt > 0) {
                    switch ($cmd) {
                        case 4:
                            $output[] = chr($data[$dp]);
                            break;
                        case 5:
                            $data[$dp] = ord($input[$ip++]);
                            break;
                        case 6:
                            $output[] = $data[$dp] . ' ';
                            break;
                        case 7:
                            while (!is_numeric($input[$ip])) {
                                if (!isset($input[$ip])) {
                                    throw new \Exception('Input have no more data, but program tries to read from it');
                                }
                                $ip++;
                            }
                            $num = 0;
                            while (isset($input[$ip]) && is_numeric($input[$ip])) {
                                $num = $num * 10 + $input[$ip];
                                $ip++;
                            }
                            $data[$dp] = $num;
                            break;
                    }
                    $cnt--;
                    $counter--;
                }
            }
            $cp++;
        }
        if ($counter < 0) {
            throw new \Exception("Limit of operations ({$this->limit}) reached before program finished!");
        }
        $this->data = $data;
        return implode('', $output);
    }
    
    function getDataCell($i) {
        return (isset($this->data) && isset($this->data[$i])) ? $this->data[$i] : null;
    }

}

