<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    require_once (dirname(__FILE__) . "/configloader.php");

    Auth::Login("abc","acc");
    //$mysql=new mysqlConnector();
    $ledgertran =new StdClass();
    $ledgertran->name="Lasitha";
    $ledgertran->contactno="12334444";
    $ledgertran->email='lasitha@gmail.com';
    $ledgertran->tranDate='2013/08/20';
    $ledgertran->gender='m';
    $ledgertran->organization="hey mail";
    $rs=SOSSData::Insert("profile",$ledgertran);
    //."<br/>";
    $ledgertran->id=$rs->result->generatedId;
    $ledgertran->name="Lasitha 1";
    echo json_encode(SOSSData::Update("profile",$ledgertran))."<br/>";

    echo json_encode(SOSSData::Query("profile",null))."<br/>";
    $mainObj = new stdClass();
    $mainObj->parameters = new stdClass();
    $mainObj->parameters->page = '0';
    $mainObj->parameters->size = '10';
    $mainObj->parameters->search = isset($_GET["q"]) ?  $_GET["q"] : "";

    $resultObj = SOSSData::ExecuteRaw("profiles_search", $mainObj);
    echo json_encode($resultObj)."<br/>";
    echo date("YmdHis");
    
    //var_dump($mysql->Delete("ledger",$ledgertran));

    

?>