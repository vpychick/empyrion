<?php

namespace empyrion;

class bp_input
{
    public readonly string $sMaterial;
    public readonly int $nCnt;

    public function __construct(string $sMaterial, int $nCnt)
    {
        $this->sMaterial=$sMaterial;
        $this->nCnt=$nCnt;
    }
}