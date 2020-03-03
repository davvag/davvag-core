<?php

require_once (__DIR__ . "/transaction_history.php");

class TransactionBuilder {

    private $transactionQueue;
    private $message;
    private $isSuccess = true;

    private $dataBag;
    private $initialObj;
    private $settings;

    function __construct($activities, $dataBag, $initialObj, $settings){
        $this->transactionQueue = array();
        $this->resultLog = new stdClass();
        $this->dataBag = $dataBag;
        $this->initialObj = $initialObj;
        $this->settings = $settings;


        foreach ($activities as $methodName => $className) {

           $this->$methodName = function() use ($className, $methodName){
               if (class_exists($className)){
                   $obj = new $className();
                   $queueItem = new stdClass();
                   $queueItem->class = $obj;
                   $queueItem->parameters = func_get_args();
                   $queueItem->methodName = $methodName;
                   $queueItem->className = $className;
                   $queueItem->handler = $obj;

                   array_push($this->transactionQueue, $queueItem);
               }else {
                   $this->message = "Class does not exist ($className) for the method : $methodName ";
                   $this->isSuccess = false;
               }

               return $this;
           };

        }
    }

    private function getTransactionId(){

    }

    public function Execute(){
        $resultObj = new stdClass();
        $resultObj->success = $this->isSuccess;
        $resultObj->message = $this->message;
        $resultObj->history = array();

        $tq = $this->transactionQueue;
        
        $tranRequest = new stdClass();
        $tranRequest->bag = $this->dataBag;
        $tranRequest->object = $this->initialObj;
        $tranRequest->settings = $this->settings;
        $resultObj->processData = $tranRequest;

        for ($i=0;$i<sizeof($tq);$i++){
            $unit = $tq[$i];

            $history = new TransactionHistory($unit);
            array_push($resultObj->history, $history);
            $tranRequest->history = $history;

            $paramArray = array($tranRequest);
            for ($j=0;$j<sizeof($unit->parameters);$j++)
                array_push($paramArray, $unit->parameters[$j]);

            $result = call_user_func_array(array($unit->handler, "Process"), $paramArray);

            if ($result === true){

            }
        }

        return $resultObj;
    }
}

?>