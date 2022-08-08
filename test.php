<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once (dirname(__FILE__) . "/configloader.php");
    require_once (dirname(__FILE__) . "/plugins/sossdata_v1/mysqlConnector.php");
    $mysql=new mysqlConnector();
    $ledgertran =new StdClass;
    $ledgertran->profileid=2;
    $ledgertran->tranid=4;
    $ledgertran->trantype='invoice';
    $ledgertran->tranDate='2013/08/20';
    $ledgertran->description='Invoice No Has been generated';
    $ledgertran->amount=100;
    var_dump($mysql->Insert("ledger",$ledgertran));
    $ledgertran->description='Invoice No Has been generated Updated';
    var_dump($mysql->Update("ledger",$ledgertran));

    var_dump($mysql->Query("ledger",""));

    echo date("YmdHis");
    //var_dump($mysql->Delete("ledger",$ledgertran));

    

?>