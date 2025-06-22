<?php

use empyrion\ecf2json;
use pychick\utils\pychickLogger;
use pychick\utils\pychickLoggerLevel;
use empyrion\item;

spl_autoload_register(function ($class_name) {
    $sClassNameShort = substr($class_name, strrpos($class_name,"\\"));
    if (str_starts_with($class_name, "pychick\utils")) include "../utils/" . $sClassNameShort . '.php';
    else if (str_starts_with($class_name, "empyrion"))include "include/" . $sClassNameShort . '.php';
    else include $class_name . '.php';
});

$log=new pychickLogger("logs/import_items.log", pychickLoggerLevel::TRACE, true, false);
$log->info("Загрузка предметов из файлов игры в базу");
$db=new empyrion\db("data\\empyrion",$log);

/** загрузка файла в массив */
try {
    $ecf=new ecf2json("data\\ItemsConfig.ecf",$log);

} catch (Exception $e) {
    $log->error("Не удалось разобрать файл");
    exit(1);
}
if(!is_array($ecf->getArray()["Item"])) {
    $log->error("В файле не обнаружено предметов");
    exit(1);
}
foreach ($ecf->getArray()["Item"] as $aItem) {
    $sName=$aItem["Name"];
    if(is_array($aItem["Volume"])) $nVolume=$aItem["Volume"]["value"]; else $nVolume=$aItem["Volume"]??0;
    if(is_array($aItem["Mass"])) $nMass=$aItem["Mass"]["value"]; else $nMass=$aItem["Mass"]??0;
    if(is_array($aItem["MarketPrice"])) $nPrice=$aItem["MarketPrice"]["value"]; else $nPrice=$aItem["MarketPrice"]??0;
    $item=new item($sName, $nVolume, $nPrice, $nMass);
    $db->saveItem($item);
    unset($item);
}
