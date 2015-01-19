<?php

class Brainfuck {
    
    private static $commands = '><+-.,:;[]#$';

    public $extio;
    public $stacked;
    public $limit;
    public $data; 
    
    function __construct($extio = false, $limit = 100000, $stacked = false) {
        $this->extio = $extio;
        $this->limit = $limit;
        $this->stacked = $stacked;
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
        $this->checkSquareBrackets($prg);
        $pattern = $this->extio ? '\>\<\+\-\.\,\:\;\[\]' : '\>\<\+\-\.\,\[\]';
        if ($this->stacked) {
            $pattern .= '\#\$';
        } else if (preg_match('/[\#\$]/', $prg)) {
            throw new \Exception('Stack operations are not allowed, please do not use "#" and "$" symbols!');
        }
        $pattern = '/[^' . $pattern . ']/';
        $p = preg_replace($pattern, '', $prg);
        return $p;
    }

    private function checkSquareBrackets($prg) {
        $s = preg_replace('/[^\[\]]/', '', $prg);
        $cnt = 0;
        $len = strlen($s);
        for ($i = 0; $i < $len; $i++) {
            if ($s[$i] == '[') {
                $cnt++;
            } else {
                $cnt--;
                if ($cnt < 0) {
                    throw new \Exception('Unexpected "]" encountered, no matching "[" precedes it!');
                }
            }
        }
        if ($cnt != 0) {
            throw new \Exception('Code has more "[" than "]"!');
        }
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
        $stack = array();
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
                        if ($dp < 0) {
                            throw new \Exception('Data pointer is decremented below zero with "<" operation');
                        }
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
                    case 10:
                        array_push($stack, $data[$dp]);
                        break;
                    case 11:
                        if (count($stack) < 1) {
                            throw new \Exception('Data stack is empty but program tried to pop from it!');
                        }
                        $data[$dp] = array_pop($stack);
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
                            if ($ip >= $inputSize) {
                                throw new \Exception('Input have no more data, but program tries to read from it with ","!');
                            }
                            $data[$dp] = ord($input[$ip++]);
                            break;
                        case 6:
                            $output[] = $data[$dp] . ' ';
                            break;
                        case 7:
                            while (!is_numeric($input[$ip])) {
                                if (!isset($input[$ip])) {
                                    throw new \Exception('Input have no more data, but program tries to read from it with ";"!');
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

