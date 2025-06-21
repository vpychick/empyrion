<?php

namespace empyrion;

define('WS_PLAYER', 1);
define('WS_PORTABLE', 2);
define('WS_SMALL',4);
define('WS_MEDIUM',8);
define('WS_ADVANCED',16);
define('WS_UNIVERSAL',32);
define('WS_CVSV',64);
define('WS_FURNACE',128);
define('WS_DECONSTRUCTOR',256);
define('WS_FOOD',512);

class item
{

    public int $nWS;
    public int $nOutputCount;

    public function __construct(public readonly string $sName, public float $nVolume, public int $nPrice, public float $nMass)
    {
    }



    protected blueprint $blueprint;


}