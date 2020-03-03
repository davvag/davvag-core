<?php

class TransactionHistory {
    public $log;
    public $className;
    public $methodName;
    public $parameters;
    public $dataBag;

    function __construct($unit){
        $this->log = array();
        $this->className = $unit->className;
        $this->methodName = $unit->methodName;
        $this->parameters = $unit->parameters;
    }

    public function Log($text){
        array_push($this->log, $text);
    }


}

?>