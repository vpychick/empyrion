<?php

namespace empyrion;

class ecf2json
{

    private array $aData=[];
    public function __construct(string $sFileName)
    {
        $fp=fopen($sFileName, "r");
        if(!$fp) throw new \Exception("Can't open .ecf file");

        while(!feof($fp)) {
            $sLine=trim(fgets($fp));

            if(str_starts_with($sLine, "#")) continue;
            
        }
    }
}