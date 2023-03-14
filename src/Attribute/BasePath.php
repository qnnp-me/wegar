<?php

namespace qnnp\wegar\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class BasePath
{
    public function __construct(public string $path = '')
    {
    }
}
