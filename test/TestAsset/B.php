<?php
namespace ZendTest\Di\TestAsset;

class B
{
    public $injectedA;

    public function __construct(A $a)
    {
        $this->injectedA = $a;
    }
}
