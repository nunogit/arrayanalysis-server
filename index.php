<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

include_once('inclibs.php');

$_wservices["submitData"] = Array("method"=>"post", "fieldtype" => Array("cellfilename"=>"FILE") );
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getArrayInformation"] = '';
$_wservices["setGroups"] = '';
$_wservices["getQCReport"] = '';
$_wservices["getArrayQCReport"] = '';
$_wservices["getFilenamesFromRawData"] = '';
$_wservices["getFilenamesFromRawData"] = '';


$ws = new BCWebService($_wservices);
$ws->listen();
