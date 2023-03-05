<?php

namespace qnnp\wegar\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Middleware
{
    public function __construct(
        public array $middleware=[]
    )
    {
    }
}
