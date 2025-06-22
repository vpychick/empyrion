<?php

use empyrion\ecf2json;
use pychick\utils\pychickLogger;
use pychick\utils\pychickLoggerLevel;


spl_autoload_register(function ($class_name) {
    $sClassNameShort = substr($class_name, strrpos($class_name,"\\"));
    if (str_starts_with($class_name, "pychick\utils")) include "../utils/" . $sClassNameShort . '.php';
    else if (str_starts_with($class_name, "empyrion"))include "include/" . $sClassNameShort . '.php';
    else include $class_name . '.php';
});

$log=new pychickLogger("logs/test_industry.log", pychickLoggerLevel::TRACE);
$log->info("Поехали");

try {
    $ecf=new ecf2json("data\\test.ecf",$log);
    $log->trace(print_r($ecf->getArray(),1));
} catch (Exception $e) {
    $log->error("Не удалось разобрать файл");
}
