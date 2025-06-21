<?php

spl_autoload_register(function ($class_name) {
    include "../utils/" . $class_name . '.php';
});

$log=new pychickLogger("logs/test_industry.log", pychickLoggerLevel::TRACE);
$log->info("Поехали");