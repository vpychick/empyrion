<?php

namespace empyrion;

use pychick\utils\pychickLogger;
use SQLite3;
use SQLite3Result;

class db extends SQLite3
{

    public function __construct(string $sDbFile, protected readonly pychickLogger $log)
    {
        parent::__construct($sDbFile);
    }

    public function loadItem(string $sItemName) : ?item {


        $sSQL="select volume, mass, price from items where name=?;";
        $aSQLp=[$sItemName];
        $res=$this->getRowSet($sSQL, $aSQLp);

        if($aRes=$res->fetchArray()) {
            $nVolume=$aRes["volume"];
            $nMass=$aRes["mass"];
            $nPrice=$aRes["price"];
        } else return null;

        return new item($sItemName, $nVolume, $nPrice, $nMass);
    }

    public function saveItem(item $item) : bool
    {

        $sSQL_Insert="name";
        $sSQL_Update="";
        $sSQL_Values="?";
        $aSQLp=[];

        if(isset($item->nMass)) {
            $sSQL_Insert.=", mass";
            $sSQL_Update.=", mass=?";
            $sSQL_Values.=", ?";
            $aSQLp[]=$item->nMass;
        }

        if(isset($item->nVolume)) {
            $sSQL_Insert.=", volume";
            $sSQL_Update.=", volume=?";
            $sSQL_Values.=", ?";
            $aSQLp[]=$item->nVolume;
        }

        if(isset($item->nPrice)) {
            $sSQL_Insert.=", price";
            $sSQL_Update.=", price=?";
            $sSQL_Values.=", ?";
            $aSQLp[]=$item->nPrice;
        }

        if(isset($item->nOutputCount)) {
            $sSQL_Insert.=", bp_output";
            $sSQL_Update.=", bp_output=?";
            $sSQL_Values.=", ?";
            $aSQLp[]=$item->nOutputCount;
        }

        if(isset($item->nWS)) {
            $sSQL_Insert.=", ws_mask";
            $sSQL_Update.=", ws_mask=?";
            $sSQL_Values.=", ?";
            $aSQLp[]=$item->nWS;
        }

        $sSQL_Update=substr($sSQL_Update,1);

        $sSQL="insert into items ($sSQL_Insert) values ($sSQL_Values) on conflict (name) do update set $sSQL_Update;";
        $aSQLp=array_merge([$item->sName],$aSQLp,$aSQLp);
        $res=$this->getRowSet($sSQL,$aSQLp);
        if(!$res) return false; else return true;
    }

    public function getRowSet(string $sSQL, array $aSQLp) : ?SQLite3Result
    {

        $this->log->debug("SQL: $sSQL / ".json_encode($aSQLp));
        $cq=$this->prepare($sSQL);
        if(!$cq) {
            $this->log->error("Не удалось подготовить SQL : $sSQL / ".json_encode($aSQLp));
            return null;
        }
        foreach (array_values($aSQLp) as $k=>$v)
        {
            if(!$cq->bindValue($k+1, $v, $this->getArgType($v))) {
                $this->log->error("Не удалось привязать параметр SQL : $sSQL / $k = $v");
                return null;
            }
        }
        if(!$res = $cq->execute())  {
            $this->log->error("Не удалось выполнить SQL : $sSQL / ".json_encode($aSQLp));
            return null;
        }

        return $res;
    }

    private function getArgType($arg) : int
    {
        return match (gettype($arg)) {
            'double' => SQLITE3_FLOAT,
            'boolean', 'integer' => SQLITE3_INTEGER,
            'NULL' => SQLITE3_NULL,
            'string' => SQLITE3_TEXT,
            default => throw new InvalidArgumentException('Не корректиный тип аругмента ' . gettype($arg)),
        };
    }

}