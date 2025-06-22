<?php

namespace empyrion;

use Exception;
use pychick\utils\pychickLogger;

class ecf2json
{
    /**
     * @var array $aData Массив преобразованных из ecf данных*/
    private array $aData=[];

    private pychickLogger $log;
    public function __construct(string $sFileName, pychickLogger $logger)
    {
        $this->log=$logger;
        $this->log->info("Разбираем файл $sFileName");

        $fp=fopen($sFileName, "r");
        if(!$fp) {
            $this->log->error("Файл $sFileName не открылся");
            throw new Exception("Can't open .ecf file");
        }

        $this->aData=$this->ParseOneLevel($fp,"");
    }

    /**
     * Разбирает очередной уровень вложенных данных, должна вызываться рекурсивно.
     *
     * @param $fp resource указатель на файл
     * @param string $sLine текущая строка, вычитанная из файла
     * @return array разобранный в массив кусок файла
     */
    private function ParseOneLevel($fp, string $sLine): array {

        $aData=[];
        $aInnerCounters=[];

        if(!empty($sLine)) {
            /** первая срока описания объекта что-то содерижит, разберем это что-то,
             * формат - пары свойство:значение, разделённые запятыми
             */
            $aPairs=explode(",",$sLine);
            if(is_array($aPairs)) {
                foreach ($aPairs as $sPair) {
                    $aPair=explode(":",trim($sPair));
                    if(is_array($aPair)) {
                        $k=trim($aPair[0]);
                        $v=trim($aPair[1]??"");
                        $this->log->trace("$k=$v");
                        $aData[$k]=$v;
                    }
                }
            }
        }

        while(!feof($fp)) {
            $sLine=trim(fgets($fp));

            if(str_starts_with($sLine, "#")) continue; /** skip comment */
            if(str_starts_with($sLine, "{")) {
                $sLine=trim(substr($sLine,1));
                list($sSection,$sData)=explode(" ",$sLine,2);
                if(str_starts_with($sSection,"+")) $sSection=substr($sSection,1); /** если объект начинается с + убираем его */
                $this->log->debug("Обнаружено подмножество $sSection");
                if(is_integer($sData)) {
                    /** индекс задан явно */
                    $nIndex=$sData;
                    $sData="";
                } else {
                    /** индекс не задан явно, используем внутренние счетчик для данного вида записей */
                    if(empty($aInnerCounters[$sSection])) $aInnerCounters[$sSection]=0;
                    $nIndex=$aInnerCounters[$sSection]++;
                }
                $aData[$sSection][$nIndex]=$this->ParseOneLevel($fp,$sData);
            } else if(str_starts_with($sLine, "}")) return $aData;
            else {
                if(preg_match_all("/((\w*)\s*:\s*((\"[a-z0-9 ,]*\")|(\w*)))/i",$sLine,$aMatches)) {
                    $sKey=$aMatches[2][0];
                    $sValue=$aMatches[3][0];
                    $this->log->trace("$sKey=$sValue");
                    if(count($aMatches[2])>1) {
                        $this->log->trace("$sKey содержит дополнительные данные, преобразуем в массив");
                        $aData[$sKey]["value"]=$sValue;
                        for($iM=1;$iM<count($aMatches[2]);$iM++) {
                            $sKey1=$aMatches[2][$iM];
                            $sValue1=$aMatches[3][$iM];
                            $this->log->trace("$sKey1=$sValue1");
                            $aData[$sKey][$sKey1]=$sValue1;
                        }
                    } else {
                        $aData[$sKey]=$sValue;
                    }
                }
            }

        }

        return $aData;
    }

    /**
     * Возвращает разобранные в массив данные
     *
     * @return array разобранные в массив данные
     */
    public function getArray(): array
    {
        return $this->aData;
    }
}